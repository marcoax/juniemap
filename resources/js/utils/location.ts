import type { LocationStato, LocationBase } from '../types/location'
import { LOCATION_COLORS, LOCATION_LABELS } from '../types/location'

export function getLocationColor(stato: LocationStato): string {
  return LOCATION_COLORS[stato]
}

export function getLocationLabel(stato: LocationStato): string {
  return LOCATION_LABELS[stato]
}

export function formatLocationCoordinates(location: LocationBase): string {
  return `${location.latitude.toFixed(6)}, ${location.longitude.toFixed(6)}`
}

export function validateLocationStato(stato: string): LocationStato | null {
  const validStates: LocationStato[] = ['attivo', 'disattivo', 'in_allarme']
  return validStates.includes(stato as LocationStato) ? (stato as LocationStato) : null
}

export function escapeHtml(text: string): string {
  const div = document.createElement('div')
  div.textContent = text
  return div.innerHTML
}

export function escapeAttribute(text: string): string {
  return text
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#x27;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
}