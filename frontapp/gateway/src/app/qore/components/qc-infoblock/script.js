import { CommandInterface } from '_scripts/qore/protocol.js';
/** Lodash ES */
import _ from 'lodash-es';

export default {
    mixins: [CommandInterface],

    data: function() {
        return {
            dispatchMap: [
                {key: 'title', path: 'component-title'},
                {key: 'content', path: 'data'},
                {key: 'reloadUrl', path: 'url'},
                {key: 'actions', path: 'component-actions'}
            ],
            title: _.get(this.options, 'component-title', null),
            content: _.get(this.options, 'data', null),
            'info-type': _.get(this.options, 'info-type', 'info'),
        };
    },

    props: ['options'],

    mounted: function() {
    },

    beforeDestroy: function() {
    },

    computed: {
        alertType: function() {
            return 'alert-' + this['info-type'];
        }
    },

    methods: {
    }
}
