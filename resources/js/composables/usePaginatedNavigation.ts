import { router } from '@inertiajs/vue3';
import type { RouteDefinition } from '@/wayfinder';

export function usePaginatedNavigation(
    currentPage: () => number,
    routeForPage: (page: number) => RouteDefinition<'get'>,
): (page: number) => void {
    return (page: number): void => {
        if (page === currentPage()) {
            return;
        }

        router.visit(routeForPage(page), {
            preserveScroll: true,
            preserveState: true,
        });
    };
}
