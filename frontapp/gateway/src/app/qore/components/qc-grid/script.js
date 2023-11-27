import { ProtocolInterface, CommandInterface } from '_scripts/qore/protocol.js';
/** Lodash ES */
import _ from 'lodash-es';
import QcgRow from './qcg-row/component.vue';
import QcgColumn from './qcg-column/component.vue';

export default {
    mixins: [CommandInterface],

    data: function() {
        return {
        };
    },

    props: ['options'],

    mounted: function() {
    },

    beforeDestroy: function() {
    },

    computed: {
    },

    watch: {
    },

    methods: {
    }
}

export const initializer = (app) => {
    app.component('qcg-row', QcgRow);
    app.component('qcg-column', QcgColumn);
}
