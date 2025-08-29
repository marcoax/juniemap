import { useCallback, useRef, useState } from 'react'
import type { LocationFull } from '../types/location'
import { ApiClient, ApiError } from '../utils/api'

export interface UseLocationDetailsResult {
  locationDetails: LocationFull | null
  isLoading: boolean
  error: string | null
  fetchLocationDetails: (id: number) => Promise<void>
  clearDetails: () => void
  clearError: () => void
}

export function useLocationDetails(): UseLocationDetailsResult {
  const [locationDetails, setLocationDetails] = useState<LocationFull | null>(null)
  const [isLoading, setIsLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const abortControllerRef = useRef<AbortController | null>(null)
  const currentRequestIdRef = useRef<number | null>(null)

  const clearDetails = useCallback(() => {
    setLocationDetails(null)
    setError(null)
  }, [])

  const clearError = useCallback(() => {
    setError(null)
  }, [])

  const fetchLocationDetails = useCallback(async (id: number) => {
    // Cancel any pending request
    if (abortControllerRef.current) {
      abortControllerRef.current.abort()
    }

    // Create new abort controller
    const controller = new AbortController()
    abortControllerRef.current = controller
    currentRequestIdRef.current = id

    try {
      setIsLoading(true)
      setError(null)

      const details = await ApiClient.getLocationDetails(id)

      // Only update state if this is still the current request
      if (!controller.signal.aborted && currentRequestIdRef.current === id) {
        setLocationDetails(details)
      }
    } catch (err) {
      if (!controller.signal.aborted && currentRequestIdRef.current === id) {
        if (err instanceof ApiError) {
          if (err.isNotFound) {
            setError('Location non trovata.')
          } else {
            setError('Errore nel caricamento dettagli.')
          }
        } else {
          setError('Errore nel caricamento dettagli.')
        }
        console.error('Location details error:', err)
      }
    } finally {
      if (!controller.signal.aborted && currentRequestIdRef.current === id) {
        setIsLoading(false)
      }
    }
  }, [])

  return {
    locationDetails,
    isLoading,
    error,
    fetchLocationDetails,
    clearDetails,
    clearError,
  }
}