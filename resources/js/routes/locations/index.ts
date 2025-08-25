import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\LocationController::index
* @see app/Http/Controllers/LocationController.php:17
* @route '/locations'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/locations',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\LocationController::index
* @see app/Http/Controllers/LocationController.php:17
* @route '/locations'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\LocationController::index
* @see app/Http/Controllers/LocationController.php:17
* @route '/locations'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LocationController::index
* @see app/Http/Controllers/LocationController.php:17
* @route '/locations'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\LocationController::index
* @see app/Http/Controllers/LocationController.php:17
* @route '/locations'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LocationController::index
* @see app/Http/Controllers/LocationController.php:17
* @route '/locations'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LocationController::index
* @see app/Http/Controllers/LocationController.php:17
* @route '/locations'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

/**
* @see \App\Http\Controllers\LocationController::search
* @see app/Http/Controllers/LocationController.php:39
* @route '/locations/search'
*/
export const search = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: search.url(options),
    method: 'get',
})

search.definition = {
    methods: ["get","head"],
    url: '/locations/search',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\LocationController::search
* @see app/Http/Controllers/LocationController.php:39
* @route '/locations/search'
*/
search.url = (options?: RouteQueryOptions) => {
    return search.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\LocationController::search
* @see app/Http/Controllers/LocationController.php:39
* @route '/locations/search'
*/
search.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: search.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LocationController::search
* @see app/Http/Controllers/LocationController.php:39
* @route '/locations/search'
*/
search.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: search.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\LocationController::search
* @see app/Http/Controllers/LocationController.php:39
* @route '/locations/search'
*/
const searchForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: search.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LocationController::search
* @see app/Http/Controllers/LocationController.php:39
* @route '/locations/search'
*/
searchForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: search.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LocationController::search
* @see app/Http/Controllers/LocationController.php:39
* @route '/locations/search'
*/
searchForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: search.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

search.form = searchForm

/**
* @see \App\Http\Controllers\LocationController::show
* @see app/Http/Controllers/LocationController.php:64
* @route '/locations/{id}'
*/
export const show = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/locations/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\LocationController::show
* @see app/Http/Controllers/LocationController.php:64
* @route '/locations/{id}'
*/
show.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    if (Array.isArray(args)) {
        args = {
            id: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        id: args.id,
    }

    return show.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\LocationController::show
* @see app/Http/Controllers/LocationController.php:64
* @route '/locations/{id}'
*/
show.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LocationController::show
* @see app/Http/Controllers/LocationController.php:64
* @route '/locations/{id}'
*/
show.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\LocationController::show
* @see app/Http/Controllers/LocationController.php:64
* @route '/locations/{id}'
*/
const showForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LocationController::show
* @see app/Http/Controllers/LocationController.php:64
* @route '/locations/{id}'
*/
showForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LocationController::show
* @see app/Http/Controllers/LocationController.php:64
* @route '/locations/{id}'
*/
showForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\LocationController::details
* @see app/Http/Controllers/LocationController.php:71
* @route '/locations/{id}/details'
*/
export const details = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: details.url(args, options),
    method: 'get',
})

details.definition = {
    methods: ["get","head"],
    url: '/locations/{id}/details',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\LocationController::details
* @see app/Http/Controllers/LocationController.php:71
* @route '/locations/{id}/details'
*/
details.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    if (Array.isArray(args)) {
        args = {
            id: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        id: args.id,
    }

    return details.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\LocationController::details
* @see app/Http/Controllers/LocationController.php:71
* @route '/locations/{id}/details'
*/
details.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: details.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LocationController::details
* @see app/Http/Controllers/LocationController.php:71
* @route '/locations/{id}/details'
*/
details.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: details.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\LocationController::details
* @see app/Http/Controllers/LocationController.php:71
* @route '/locations/{id}/details'
*/
const detailsForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: details.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LocationController::details
* @see app/Http/Controllers/LocationController.php:71
* @route '/locations/{id}/details'
*/
detailsForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: details.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LocationController::details
* @see app/Http/Controllers/LocationController.php:71
* @route '/locations/{id}/details'
*/
detailsForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: details.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

details.form = detailsForm

const locations = {
    index,
    search,
    show,
    details,
}

export default locations