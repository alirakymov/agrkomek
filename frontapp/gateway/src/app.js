/** Lodash ES */
import _ from 'lodash-es';
/** Vue object  */
import { createApp, defineAsyncComponent } from 'vue';
/** Qore vue application */
import Qore from './app/qore.vue';
/** Qore initializer */
import QoreInitializer from './app/qore/initializer.js';

const app = createApp(Qore);
QoreInitializer(app);

app.mount('#qore-app');

