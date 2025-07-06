import { route as ziggyRoute } from 'ziggy-js';

declare global {
    let route: typeof ziggyRoute;
}

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        route: typeof ziggyRoute;
    }
}
