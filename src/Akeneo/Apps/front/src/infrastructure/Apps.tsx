import * as React from 'react';
import {Index} from '../application/apps/pages/Index';
import {RouterContext, RouterInterface} from '../application/shared/router';
import {TranslateContext, TranslateInterface} from '../application/shared/translate';
import {composeProviders} from './compose-providers';
import {LegacyContext} from './legacy-context';
import {ViewBuilder} from './pim-view/view-builder';

interface Props {
    router: RouterInterface;
    translate: TranslateInterface;
    viewBuilder: ViewBuilder;
}

export const Apps = ({router, translate, viewBuilder}: Props) => {
    const Providers = composeProviders(
        [RouterContext.Provider, router],
        [TranslateContext.Provider, translate],
        [
            LegacyContext.Provider,
            {
                viewBuilder,
            },
        ]
    );

    return (
        <Providers>
            <Index />
        </Providers>
    );
};
