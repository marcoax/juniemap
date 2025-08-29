import type { ApiError, ApiResponse, LocationBase, LocationFull, LocationSearchParams } from '../types/location'

export class ApiClient {
  private static async request<T>(url: string, options: RequestInit = {}): Promise<T> {
    const response = await fetch(url, {
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...options.headers,
      },
      ...options,
    })

    if (!response.ok) {
      const error: ApiError = await response.json().catch(() => ({
        message: `HTTP Error ${response.status}`,
        error: 'NETWORK_ERROR',
      }))
      throw new ApiError(error.message, response.status, error.error)
    }

    return response.json()
  }

  static async searchLocations(params: LocationSearchParams): Promise<LocationBase[]> {
    const searchParams = new URLSearchParams()
    
    if (params.search) {
      searchParams.set('search', params.search)
    }
    
    if (params.stato) {
      searchParams.set('stato', params.stato)
    }

    const url = `/locations/search?${searchParams.toString()}`
    const response = await this.request<{ data: LocationBase[] }>(url)
    return response.data || []
  }

  static async getLocationDetails(id: number): Promise<LocationFull> {
    const response = await this.request<{ data: LocationFull }>(`/locations/${id}/details`)
    return response.data
  }
}

export class ApiError extends Error {
  constructor(
    message: string,
    public readonly status: number = 500,
    public readonly code?: string
  ) {
    super(message)
    this.name = 'ApiError'
  }

  get isNotFound(): boolean {
    return this.status === 404
  }

  get isClientError(): boolean {
    return this.status >= 400 && this.status < 500
  }

  get isServerError(): boolean {
    return this.status >= 500
  }
}