export type LocationStato = 'attivo' | 'disattivo' | 'in_allarme'

export interface LocationStatoDetails {
  value: LocationStato
  label: string
  color: string
  css_class: string
}

export interface LocationBase {
  id: number
  titolo: string
  indirizzo: string
  latitude: number
  longitude: number
  stato: LocationStato
}

export interface LocationFull extends LocationBase {
  descrizione?: string | null
  orari_apertura?: string | null
  prezzo_biglietto?: string | null
  sito_web?: string | null
  telefono?: string | null
  note_visitatori?: string | null
  created_at?: string
  updated_at?: string
}

export interface LocationWithDetails extends Omit<LocationFull, 'stato'> {
  stato: LocationStatoDetails
}

export interface LocationSearchFilters {
  search: string | null
  stato: LocationStato | null
}

export interface LocationSearchParams {
  search?: string
  stato?: LocationStato
}

export interface ApiError {
  message: string
  error?: string
}

export interface ApiResponse<T> {
  data: T
}

export const LOCATION_COLORS = {
  attivo: '#10B981',
  disattivo: '#9CA3AF',
  in_allarme: '#EF4444',
} as const

export const LOCATION_LABELS = {
  attivo: 'Attivo',
  disattivo: 'Disattivo', 
  in_allarme: 'In Allarme',
} as const