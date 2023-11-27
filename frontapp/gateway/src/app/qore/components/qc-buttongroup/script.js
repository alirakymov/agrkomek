import { ProtocolInterface, CommandInterface } from '_scripts/qore/protocol.js';
/** Lodash ES */
import _ from 'lodash-es';

export default {
    mixins: [CommandInterface],

    data: function() {
        return {
            name: _.get(this.options, 'name', 'button-group'),
            buttons: _.get(this.options, 'buttons', []),
        };
    },

    props: ['options'],

    mounted: function() {
    },

    computed: {
    },

    methods: {
        getClass(button) {
            return _.get(button, 'class', 'btn-alt-secondary');
        },
        getUrl(button) {
            return _.get(button, 'url', 'javascript:void(0);');
        },
        action(button, e) {
            let actionUri = _.get(button, 'actionUri', false);
            if (actionUri) {
                this.reaction(button, e);
            }
        },
        withIcon(button) {
            return _.get(button, 'icon', false) !== false;
        },
    },
}
