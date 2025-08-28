import { Head, usePage } from '@inertiajs/react'
import { useEffect, useRef, useState } from 'react'

const COLORS = {
  primary: '#2563EB',
  success: '#10B981',
  danger: '#EF4444',
  muted: '#9CA3AF',
}

type LocationLite = {
  id: number
  titolo: string
  indirizzo: string
  latitude: number
  longitude: number
  stato: 'attivo' | 'disattivo' | 'in_allarme'
}

type LocationFull = LocationLite & {
  descrizione?: string | null
  orari_apertura?: string | null
  prezzo_biglietto?: string | null
  sito_web?: string | null
  telefono?: string | null
  note_visitatori?: string | null
}

type PageProps = {
  filters: { search: string | null; stato: string | null }
  locations: LocationLite[]
  googleMapsApiKey?: string | null
  googleMapsApiKeyMissing?: boolean
}

export default function MapPage() {
  const { props } = usePage<PageProps>()
  const initialLocations = props.locations ?? []

  const [locations, setLocations] = useState<LocationLite[]>(initialLocations)
  const [selected, setSelected] = useState<LocationFull | null>(null)
  const [searchQuery, setSearchQuery] = useState<string>(props.filters?.search ?? '')
  const [currentFilter, setCurrentFilter] = useState<'attivo' | 'disattivo' | 'in_allarme' | 'tutti'>(
    (props.filters?.stato as any) || 'tutti',
  )
  const [isLoading, setIsLoading] = useState<boolean>(false)
  const [sidebarOpen, setSidebarOpen] = useState<boolean>(false)

  // Open sidebar by default on medium+ screens
  useEffect(() => {
    if (typeof window !== 'undefined' && window.innerWidth >= 768) {
      setSidebarOpen(true)
    }
  }, [])
  const [err, setErr] = useState<string | null>(null)
  // Trigger to force-close InfoWindow on Reset even if opened via marker click
  const [resetTick, setResetTick] = useState<number>(0)

  useEffect(() => {
    const controller = new AbortController()
    const t = setTimeout(async () => {
      try {
        setIsLoading(true)
        setErr(null)
        const params = new URLSearchParams()
        if (searchQuery) params.set('search', searchQuery)
        const stato = currentFilter === 'tutti' ? '' : currentFilter
        if (stato) params.set('stato', stato)
        const res = await fetch(`/locations/search?${params.toString()}`, {
          signal: controller.signal,
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        if (!res.ok) throw new Error('Errore rete: ' + res.status)
        const json = await res.json()
        setLocations(json.data ?? json)
      } catch (e: any) {
        if (e?.name !== 'AbortError') setErr('Errore durante la ricerca. Riprova.')
      } finally {
        setIsLoading(false)
      }
    }, 300)
    return () => {
      controller.abort()
      clearTimeout(t)
    }
  }, [searchQuery, currentFilter])

  return (
    <div className="flex h-dvh w-full flex-col bg-white text-[#111] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
      <Head title="JunieMap" />

      <TopBar
        total={initialLocations.length}
        filtered={locations.length}
        search={searchQuery}
        setSearch={setSearchQuery}
        stato={currentFilter}
        setStato={setCurrentFilter}
        onReset={() => {
          setSearchQuery('')
          setCurrentFilter('tutti')
          setLocations(initialLocations)
          setSelected(null)
          setResetTick((t) => t + 1)
        }}
        sidebarOpen={sidebarOpen}
        setSidebarOpen={setSidebarOpen}
        loading={isLoading}
        apiKeyMissing={!!props.googleMapsApiKeyMissing}
      />

      <div className="flex h-full w-full overflow-hidden">
        <LocationsSidebar open={sidebarOpen} locations={locations} onSelect={(l) => setSelected(l as any)} loading={isLoading} onClose={() => setSidebarOpen(false)} />
        <div className="relative flex-1">
          {props.googleMapsApiKeyMissing ? (
            <div className="flex h-full items-center justify-center p-6 text-center">
              <div className="max-w-md rounded-md border p-6">
                <div className="mb-2 text-lg font-semibold" style={{ color: COLORS.danger }}>
                  Errore: Google Maps API key mancante
                </div>
                <p className="text-sm opacity-80">
                  Imposta VITE_GOOGLE_MAPS_API_KEY nel file .env e ricompila gli asset con
                  <code className="ml-1 rounded bg-neutral-100 px-1 py-0.5 text-xs dark:bg-neutral-800">npm run dev</code>.
                </p>
              </div>
            </div>
          ) : (
            <GoogleMap apiKey={props.googleMapsApiKey!} points={locations} selected={selected} onSelect={setSelected} resetTick={resetTick} />
          )}
          {err && (
            <div className="absolute bottom-4 left-1/2 -translate-x-1/2 rounded bg-[#111] px-3 py-2 text-sm text-white shadow dark:bg-white dark:text-black">
              {err}
            </div>
          )}
        </div>
      </div>
    </div>
  )
}

function TopBar(props: {
  total: number
  filtered: number
  search: string
  setSearch: (v: string) => void
  stato: 'attivo' | 'disattivo' | 'in_allarme' | 'tutti'
  setStato: (v: 'attivo' | 'disattivo' | 'in_allarme' | 'tutti') => void
  onReset: () => void
  sidebarOpen: boolean
  setSidebarOpen: (v: boolean) => void
  loading: boolean
  apiKeyMissing: boolean
}) {
  return (
    <div className="flex flex-wrap items-center gap-2 border-b p-3">
      <button onClick={() => props.setSidebarOpen(!props.sidebarOpen)} className="rounded border px-3 py-2 text-sm hover:bg-neutral-50 dark:hover:bg-neutral-900" aria-label="Toggle sidebar">
        ☰
      </button>
      <input
        value={props.search}
        onChange={(e) => props.setSearch(e.target.value)}
        placeholder="Cerca locations..."
        className="w-full sm:max-w-[480px] flex-1 min-w-0 rounded border px-3 py-2 text-sm outline-none focus:ring-2"
        style={{ borderColor: '#e5e7eb', boxShadow: '0 0 0 0 rgba(0,0,0,0)' }}
      />
      <select value={props.stato} onChange={(e) => props.setStato(e.target.value as any)} className="w-full sm:w-auto rounded border px-2 py-2 text-sm stato-select">
        <option value="tutti">Tutti gli stati</option>
        <option value="attivo">attivo</option>
        <option value="disattivo">disattivo</option>
        <option value="in_allarme">in_allarme</option>
      </select>
      <button onClick={props.onReset} className="w-full sm:w-auto rounded bg-[#f3f4f6] px-3 py-2 text-sm hover:bg-[#e5e7eb] dark:bg-neutral-900 dark:hover:bg-neutral-800">
        Reset
      </button>
      <div className="w-full sm:w-auto sm:ml-auto flex flex-wrap items-center gap-2 text-xs justify-between sm:justify-end">
        <span className="rounded bg-neutral-100 px-2 py-1 dark:bg-neutral-900">Totali: {props.total}</span>
        <span className="rounded bg-neutral-100 px-2 py-1 dark:bg-neutral-900">Filtrate: {props.filtered}</span>
        {props.loading && (
          <span className="text-[13px]" style={{ color: COLORS.primary }}>
            Aggiornamento mappa...
          </span>
        )}
        {props.apiKeyMissing && (
          <span className="text-[13px]" style={{ color: COLORS.danger }}>
            API key mancante
          </span>
        )}
      </div>
    </div>
  )
}

function LocationsSidebar(props: { open: boolean; locations: LocationLite[]; onSelect: (l: LocationLite) => void; loading: boolean; onClose?: () => void }) {
  return (
    <>
      {/* Mobile overlay */}
      {props.open && (
        <div className="fixed inset-0 z-20 md:hidden">
          <div className="absolute inset-0 bg-black/40" onClick={props.onClose} />
          <div className="absolute left-0 top-0 h-full w-full border-r bg-white dark:bg-[#0a0a0a] shadow-lg">
            <div className="flex items-center justify-between border-b p-3 text-sm">
              <div className="font-medium">Locations</div>
              <button onClick={props.onClose} className="rounded border px-2 py-1 text-xs">Chiudi</button>
            </div>
            <div className="h-full overflow-auto p-2">
              {props.loading ? (
                <SidebarSkeleton />
              ) : props.locations.length === 0 ? (
                <div className="p-3 text-sm opacity-70">Nessun risultato</div>
              ) : (
                <ul className="flex flex-col gap-2">
                  {props.locations.map((l) => (
                    <li key={l.id}>
                      <button onClick={() => { props.onSelect(l); if (props.onClose) { props.onClose(); } }} className="w-full rounded border p-2 text-left text-sm hover:bg-neutral-50 dark:hover:bg-neutral-900">
                        <div className="flex items-center justify-between">
                          <span className="font-medium">{l.titolo}</span>
                          <StatusBadge stato={l.stato} />
                        </div>
                        <div className="mt-1 line-clamp-1 text-xs opacity-70">{l.indirizzo}</div>
                      </button>
                    </li>
                  ))}
                </ul>
              )}
            </div>
          </div>
        </div>
      )}

      {/* Desktop docked */}
      <div className={`${props.open ? 'w-80' : 'w-0'} relative hidden shrink-0 transition-[width] duration-200 md:block`}>
        <div className={`absolute left-0 top-0 h-full ${props.open ? 'w-80' : 'w-0'} overflow-hidden border-r bg-white dark:bg-[#0a0a0a]`}>
          <div className="flex items-center justify-between border-b p-3 text-sm">
            <div className="font-medium">Locations</div>
            <div className="text-xs opacity-70">{props.locations.length}</div>
          </div>
          <div className="h-full overflow-auto p-2">
            {props.loading ? (
              <SidebarSkeleton />
            ) : props.locations.length === 0 ? (
              <div className="p-3 text-sm opacity-70">Nessun risultato</div>
            ) : (
              <ul className="flex flex-col gap-2">
                {props.locations.map((l) => (
                  <li key={l.id}>
                    <button onClick={() => props.onSelect(l)} className="w-full rounded border p-2 text-left text-sm hover:bg-neutral-50 dark:hover:bg-neutral-900">
                      <div className="flex items-center justify-between">
                        <span className="font-medium">{l.titolo}</span>
                        <StatusBadge stato={l.stato} />
                      </div>
                      <div className="mt-1 line-clamp-1 text-xs opacity-70">{l.indirizzo}</div>
                    </button>
                  </li>
                ))}
              </ul>
            )}
          </div>
        </div>
      </div>
    </>
  )
}

function SidebarSkeleton() {
  return (
    <ul className="flex flex-col gap-2 p-2">
      {Array.from({ length: 8 }).map((_, i) => (
        <li key={i} className="animate-pulse rounded border p-3">
          <div className="h-3 w-2/3 rounded bg-neutral-200 dark:bg-neutral-800" />
          <div className="mt-2 h-2 w-1/2 rounded bg-neutral-200 dark:bg-neutral-800" />
        </li>
      ))}
    </ul>
  )
}

function StatusBadge({ stato }: { stato: LocationLite['stato'] }) {
  const color = stato === 'attivo' ? COLORS.success : stato === 'in_allarme' ? COLORS.danger : COLORS.muted
  return (
    <span className="rounded px-2 py-0.5 text-xs font-medium" style={{ backgroundColor: `${color}22`, color }}>
      {stato}
    </span>
  )
}

function GoogleMap(props: { apiKey: string; points: LocationLite[]; selected: LocationFull | null; onSelect: (l: LocationFull | null) => void; resetTick: number }) {
  const mapRef = useRef<HTMLDivElement | null>(null)
  const mapObj = useRef<google.maps.Map | null>(null)
  const markers = useRef<Record<number, google.maps.marker.AdvancedMarkerElement>>({})
  const infoWindowRef = useRef<google.maps.InfoWindow | null>(null)

  useEffect(() => {
    if (typeof window === 'undefined') return
    if ((window as any)._gmapsLoaded) return
    const s = document.createElement('script')
    s.src = `https://maps.googleapis.com/maps/api/js?key=${props.apiKey}&libraries=marker`
    s.async = true
    s.onload = () => ((window as any)._gmapsLoaded = true)
    s.onerror = () => console.error('Google Maps failed to load')
    document.head.appendChild(s)
  }, [props.apiKey])

  useEffect(() => {
    let t: any
    function init() {
      if (!(window as any).google || !mapRef.current) {
        t = setTimeout(init, 100)
        return
      }
      const center = { lat: 42.5, lng: 12.5 }
      mapObj.current = new google.maps.Map(mapRef.current, {
        center,
        zoom: 6,
        mapId: 'junie-map',
        clickableIcons: false,
        zoomControl: true,
        zoomControlOptions: { position: google.maps.ControlPosition.RIGHT_BOTTOM },
      })
      infoWindowRef.current = new google.maps.InfoWindow({ maxWidth: 320 })
      renderMarkers()
      fitBounds()
    }
    init()
    return () => clearTimeout(t)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  useEffect(() => {
    if (!mapObj.current || !(window as any).google) return
    renderMarkers()
    fitBounds()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [props.points])

  // Close info window when Reset is clicked, even if no selection change occurred
  useEffect(() => {
    infoWindowRef.current?.close()
  }, [props.resetTick])

  useEffect(() => {
    if (!mapObj.current) return
    if (!props.selected) {
      // Close any open info window when selection is cleared (e.g., on Reset)
      infoWindowRef.current?.close()
      return
    }
    const pos = { lat: props.selected.latitude, lng: props.selected.longitude }
    mapObj.current.panTo(pos)
    const targetZoom = Math.min(15, Math.max(mapObj.current.getZoom() ?? 6, 12))
    mapObj.current.setZoom(targetZoom)
    const m = markers.current[props.selected.id]
    if (m) openInfoWindow(m, props.selected.id)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [props.selected])

  function fitBounds() {
    if (!mapObj.current) return
    const count = props.points.length
    if (count === 0) {
      return
    }
    if (count === 1) {
      const only = props.points[0]
      // Reduce zoom when only one point is shown to avoid over-zooming
      mapObj.current.setCenter({ lat: only.latitude, lng: only.longitude })
      mapObj.current.setZoom(10)
      return
    }
    const bounds = new google.maps.LatLngBounds()
    props.points.forEach((p) => bounds.extend({ lat: p.latitude, lng: p.longitude }))
    mapObj.current.fitBounds(bounds)
  }

  function renderMarkers() {
    const g = (window as any).google as typeof google
    const currentIds = new Set(props.points.map((p) => p.id))
    Object.entries(markers.current).forEach(([id, m]) => {
      if (!currentIds.has(Number(id))) {
        m.map = null as any
        delete markers.current[Number(id)]
      }
    })
    props.points.forEach((p) => {
      let marker = markers.current[p.id]
      if (!marker) {
        marker = new g.maps.marker.AdvancedMarkerElement({
          map: mapObj.current!,
          position: { lat: p.latitude, lng: p.longitude },
          title: p.titolo,
        })
        marker.addListener('click', () => openInfoWindow(marker!, p.id))
        markers.current[p.id] = marker
      } else {
        marker.position = new g.maps.LatLng(p.latitude, p.longitude)
      }
    })
  }

  async function openInfoWindow(marker: google.maps.marker.AdvancedMarkerElement, id: number) {
    if (!infoWindowRef.current) return
    infoWindowRef.current.close()
    infoWindowRef.current.setContent(`<div class="p-2 text-sm"><div class="mb-1 font-semibold">Caricamento...</div></div>`)
    infoWindowRef.current.open({ map: mapObj.current!, anchor: marker })
    try {
      const res = await fetch(`/locations/${id}/details`)
      const json = await res.json()
      const l: LocationFull = (json.data ?? json) as any
      const statoColor = l.stato === 'attivo' ? COLORS.success : l.stato === 'in_allarme' ? COLORS.danger : COLORS.muted
      const html = `
        <div class="max-w-[320px] text-sm">
          <div class="mb-1 flex items-start justify-between gap-2">
            <div>
              <div class="font-semibold">${escapeHtml(l.titolo)}</div>
              <div class="text-xs opacity-70">${escapeHtml(l.indirizzo)}</div>
            </div>
            <span style="background:${statoColor}22;color:${statoColor};" class="rounded px-2 py-0.5 text-xs font-medium">${l.stato}</span>
          </div>
          ${l.descrizione ? `<div class="mt-2">${escapeHtml(l.descrizione)}</div>` : ''}
          <div class="mt-2 space-y-1">
            ${l.orari_apertura ? `<div>🕐 ${escapeHtml(l.orari_apertura)}</div>` : ''}
            ${l.prezzo_biglietto ? `<div>💰 ${escapeHtml(l.prezzo_biglietto)}</div>` : ''}
            ${l.telefono ? `<div>📞 ${escapeHtml(l.telefono)}</div>` : ''}
            ${l.sito_web ? `<div>🌐 <a href="${escapeAttr(l.sito_web)}" target="_blank" rel="noopener" class="underline">Sito web</a></div>` : ''}
            ${l.note_visitatori ? `<div>ℹ️ ${escapeHtml(l.note_visitatori)}</div>` : ''}
          </div>
        </div>`
      infoWindowRef.current.setContent(html)
    } catch (e) {
      infoWindowRef.current.setContent('<div class="p-2 text-sm text-red-600">Errore nel caricamento dettagli.</div>')
    }
  }

  return <div ref={mapRef} className="h-full w-full" />
}

function escapeHtml(s: string) {
  return s.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;')
}

function escapeAttr(s: string) {
  return escapeHtml(s)
}
