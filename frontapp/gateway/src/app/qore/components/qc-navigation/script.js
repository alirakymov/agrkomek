import { CommandInterface } from '_scripts/qore/protocol.js';
/** Lodash ES */
import _ from 'lodash-es';

export default {
    mixins: [CommandInterface],

    data: function() {
        return {
            name: 'navpills',
            items: _.get(this.options, 'items', []),
        };
    },

    props: ['options'],

    mounted: function() {
    },

    computed: {
    }
}
