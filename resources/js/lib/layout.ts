import { Arrayable } from '@vueuse/core';
import { Component, h as hyperscript, VNode } from 'vue';

// https://github.com/vuejs/language-tools/tree/master/packages/component-type-helpers
type ComponentProps<T> = T extends new (...args: any) => { $props: infer P }
    ? NonNullable<P>
    : T extends (props: infer P, ...args: any) => any
      ? P
      : Record<string, never>;

type Renderer<T extends VNode> = (
    h: typeof hyperscript,
    page: VNode,
) => T & {
    inheritAttrs?: boolean;
};

type LayoutComponent = Component & {
    layout?: Renderer<VNode> | Arrayable<VNode>;
};

export function getLayout<T extends LayoutComponent>(component: T, props?: () => ComponentProps<T>): Renderer<VNode> {
    const getProps = props ?? (() => ({}));

    return (h, page) => {
        const child = h(component, getProps(), () => page);

        if (component.layout && typeof component.layout === 'function') {
            const parent = component.layout(h, child);
            return h(parent, parent.props ?? {}, () => child);
        }

        return child;
    };
}

export function getLayoutStack(...renderers: Renderer<VNode>[]): Renderer<VNode> {
    return (h, page) => {
        return renderers.toReversed().reduce((child, renderer) => {
            return renderer(h, child);
        }, page);
    };
}
