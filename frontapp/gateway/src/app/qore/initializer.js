/** Interface Gateway */
import { ProtocolInterface } from './protocol.js';
/** Each module as async component */
import { defineAsyncComponent } from 'vue';
/** Emitter */
import Mitt from 'mitt';
/** Axios http client */
import Axios from 'axios';
/** Vue Draggable */
import { VueDraggableNext } from 'vue-draggable-next';
/** Flatpickr (datetime picker) */
import FlatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import 'flatpickr/dist/themes/airbnb.css';

import { createYmapsOptions, initYmaps } from 'vue-yandex-maps';

/** Vue json viewer */
import JsonViewer from 'vue3-json-viewer';
/** Json viewer styles */
import '_node/vue3-json-viewer/dist/index.css';
/** Vue ColorPicker */
import { Chrome, create as ColorPicker } from '@ckpack/vue-color/dist/index.js';
/** Vue WysiwygEditor */
import CKEditor from '@ckeditor/ckeditor5-vue';
/** Vue kinescope plugin */
import KinescopePlayer from '@kinescope/vue-kinescope-player/src/index.js'
/** Vue mask */
import { VueMaskDirective } from 'v-mask';

export default (app) => {
    /** Initialize vendor components */
    /** -- draggable */
    app.component('draggable', VueDraggableNext);
    /** -- vue-flatpickr-component */
    app.component('flat-pickr', FlatPickr);
    /** -- vue-json-viewer */
    app.use(JsonViewer);
    /** -- vue-monaco-editor */
    app.component('MonacoEditor', defineAsyncComponent(() => {
        return import('monaco-editor-vue3');
    }));
    /** -- vue-kinescope */
    KinescopePlayer(app);
    /** -- Vue CKEditor */
    app.use(CKEditor);

    const vMaskVue3 = {
        beforeMount: VueMaskDirective.bind,
        updated: VueMaskDirective.componentUpdated,
        unmounted: VueMaskDirective.unbind
    };

    app.directive('mask', vMaskVue3);

    const settings = {
        apiKey: '6438dbae-14c9-4e50-8df4-9672951f5190',
        lang: 'ru_RU',
        coordorder: 'latlong',
        enterprise: false,
        version: '2.1'
    }

    let a = createYmapsOptions({
        apikey: '6438dbae-14c9-4e50-8df4-9672951f5190',
    });

    initYmaps();

    // app.use(a);

    /** -- Vue ColorPicker */
    Chrome.name = 'color-picker';
    app.use(ColorPicker({
        components: [Chrome]
    }));

    /** Import theme styles */
    import('_styles/main.scss');

    /** Initialize Qore layouts */
    [
        'ql-main',
        'ql-login',
    ].forEach((component) => {
        let vueComponent = require(['.', 'layouts', component, 'component'].join('/') + '.vue');
        app.component(component, vueComponent.default);

        if (vueComponent.initializer) {
            vueComponent.initializer(app);
        }
    });

    /** Initialize Qore components */
    [
        'qc-block',
        'qc-buttongroup',
        'qc-consultancy',
        'qc-description',
        'qc-dialog',
        'qc-form',
        'qc-grid',
        'qc-htmlcontent',
        'qc-infoblock',
        'qc-machinery-form',
        'qc-modal',
        'qc-navbar',
        'qc-navigation',
        'qc-pagination',
        'qc-simpletile',
        'qc-stomp',
        'qc-story-form',
        'qc-table',
        'qc-tabs',
        'qc-tabs-improve',
        'qc-tile',
    ].forEach((component) => {
        let vueComponent = require(['.', 'components', component, 'component'].join('/') + '.vue')
        app.component(component, vueComponent.default);

        if (vueComponent.initializer) {
            vueComponent.initializer(app);
        }
    });

    /** Define global event bus of Vue as property */
    app.config.globalProperties.$bus = Mitt();
    /** Define global request port of Vue as property */
    app.config.globalProperties.$axios = Axios;
    /** Define global components protocol of system as property */
    app.config.globalProperties.$protocol = ProtocolInterface;

    /** Define vue directive for outside click */
    app.directive('click-outside', {
        bind: function (el, binding, vnode) {
            console.log('hui');
            el.clickOutsideEvent = function (event) {
                if (!(el == event.target || el.contains(event.target))) {
                    vnode.context[binding.expression](event);
                }
            };
            document.body.addEventListener('click', el.clickOutsideEvent)
        },
        unbind: function (el) {
            document.body.removeEventListener('click', el.clickOutsideEvent)
        },
    });
};
