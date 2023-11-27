import { ProtocolInterface, CommandInterface } from '_scripts/qore/protocol.js';

export default {
    mixins: [CommandInterface],

    data: function() {
        return {
            dispatchMap: [
                'title',
                {key: 'content', path: 'data'},
                {key: 'reloadUrl', path: 'url'},
                {key: 'actions', path: 'component-actions'}
            ],
            content: this.options.data,
            reloadUrl: _object.get(this.options, 'url', null),
            title: _object.get(this.options, 'component-title', null),
            actions: _object.get(this.options, 'component-actions', {}),
        };
    },

    props: ['options'],

    mounted: function() {
    },

    beforeDestroy: function() {
    },

    computed: {
        dragOptions: function() {
            return {
                disabled: this.draggable === false,
            };
        },
    },

    methods: {

        componentActionIcon: function(action) {
            return _object.get(action, 'icon', 'fa fa-cog');
        },

        componentActionClick: function(action, e) {
            e.preventDefault();
            this.$axios.get(action.actionUri);
        },

    }
}
