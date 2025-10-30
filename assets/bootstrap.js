import { startStimulusApp } from '@symfony/stimulus-bridge';
import { registerReactControllerComponents } from '@symfony/ux-react';
// import './react/controllers';


// Registers Stimulus controllers from controllers.json and in the controllers/ directory
export const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.[jt]sx?$/
));

// Looking for components under assets/react/components
registerReactControllerComponents(require.context('./react/components', true, /\.(j|t)sx?$/));

// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
