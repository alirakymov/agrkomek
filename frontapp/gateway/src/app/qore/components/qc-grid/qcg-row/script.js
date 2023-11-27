import { ProtocolInterface, CommandInterface } from '_scripts/qore/protocol.js';
/** Lodash ES */
import _ from 'lodash-es';

export default {
    mixins: [CommandInterface],

    data: function() {
        return {
            styleClass: _.get(this.options, 'style-class', ''),
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
