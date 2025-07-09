import { route as ziggyRoute } from 'ziggy-js';

declare global {
    let route: typeof ziggyRoute;
}

declare module 'vue' {
    interface ComponentCustomProperties {
        route: typeof ziggyRoute;
    }
}
