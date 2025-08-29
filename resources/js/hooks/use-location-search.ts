import { useCallback, useEffect, useRef, useState } from 'react'
import type { LocationBase, LocationSearchParams } from '../types/location'
import { ApiClient, ApiError } from '../utils/api'

export interface UseLocationSearchResult {
  locations: LocationBase[]
  isLoading: boolean
  error: string | null
  searchLocations: (params: LocationSearchParams) => Promise<void>
  clearError: () => void
}

export function useLocationSearch(
  initialLocations: LocationBase[] = [],
  debounceMs = 300
): UseLocationSearchResult {
  // Ensure initial locations is always an array
  const safeInitialLocations = Array.isArray(initialLocations) ? initialLocations : []
  const [locations, setLocations] = useState<LocationBase[]>(safeInitialLocations)
  const [isLoading, setIsLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const abortControllerRef = useRef<AbortController | null>(null)
  const timeoutRef = useRef<NodeJS.Timeout | null>(null)

  const clearError = useCallback(() => {
    setError(null)
  }, [])

  const searchLocations = useCallback(async (params: LocationSearchParams) => {
    // Cancel any pending request
    if (abortControllerRef.current) {
      abortControllerRef.current.abort()
    }

    // Clear any pending timeout
    if (timeoutRef.current) {
      clearTimeout(timeoutRef.current)
    }

    // Create new abort controller
    const controller = new AbortController()
    abortControllerRef.current = controller

    // Debounce the search
    timeoutRef.current = setTimeout(async () => {
      try {
        setIsLoading(true)
        setError(null)

        const results = await ApiClient.searchLocations(params)
        
        if (!controller.signal.aborted) {
          // Ensure results is always an array
          const safeResults = Array.isArray(results) ? results : []
          setLocations(safeResults)
        }
      } catch (err) {
        if (!controller.signal.aborted) {
          if (err instanceof ApiError) {
            setError(err.message)
          } else {
            setError('Errore durante la ricerca. Riprova.')
          }
          console.error('Search error:', err)
        }
      } finally {
        if (!controller.signal.aborted) {
          setIsLoading(false)
        }
      }
    }, debounceMs)
  }, [debounceMs])

  // Cleanup on unmount
  useEffect(() => {
    return () => {
      if (abortControllerRef.current) {
        abortControllerRef.current.abort()
      }
      if (timeoutRef.current) {
        clearTimeout(timeoutRef.current)
      }
    }
  }, [])

  return {
    locations,
    isLoading,
    error,
    searchLocations,
    clearError,
  }
}