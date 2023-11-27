import { ProtocolInterface, CommandInterface } from '_scripts/qore/protocol.js';
/** Lodash ES */
import _ from 'lodash-es';

export default {
    mixins: [CommandInterface],

    data: function() {
        let actions = _.get(this.options, 'actions', []);

        if (actions instanceof Object) {
            actions = Object.values(actions);
        }

        return {
            dispatchMap: ['title'],
            title: _.get(this.options, 'title', null),
            actionsHandler: _.get(this.options, 'actionsHandler', null),
            isBlock: _.get(this.options, 'isBlock', true),
            indents: _.get(this.options, 'indents', false),
            fullscreen: false,
            contentShown: true,
            actions,
        };
    },

    props: ['options'],

    mounted: function() {

    },

    updated() {
        this.$emit('updated');
    },

    beforeDestroy: function() {
    },

    computed: {
        id: function() {
            return (this.name + '-block').replace('\\', '');
        },

        computedActions: function() {
            let $this = this;

            if (this.actions instanceof Object) {
                this.actions = Object.values(this.actions);
            }

            return _.concat(this.actions, this.isBlock ? [
                {
                    label: 'На весь экран',
                    icon: this.fullscreen ? 'fa fa-compress' : 'fa fa-expand',
                    dataAction: 'fullscreen_',
                    action: function() {
                        $this.toggleFullScreen();
                    }
                },
                {
                    label: 'Свернуть',
                    icon: this.contentShown ?  'fa fa-chevron-up': 'fa fa-chevron-down',
                    dataAction: 'content_',
                    action: function() {
                        $this.toggleContent();
                    }
                },
                {
                    label: 'Закрыть',
                    icon: 'fa fa-times',
                    dataAction: '',
                    action: function() {
                        $this.cdestroy();
                    }
                },
            ] : []);
        }
    },

    watch: {
        options: function(options) {

            this.title = _.get(options, 'title', '');

            let actions = _.get(options, 'actions', []);
            actions = actions instanceof Object ? Object.values(actions) : actions;
            this.actions = actions;
        },
    },

    methods: {

        pickIcon: function(action) {
            return _.get(action, 'icon', 'fa fa-cog');
        },

        processAction: function(actionOptions, e) {
            let action = _.get(actionOptions, 'action', _.get(actionOptions, 'actionUri', false));
            if (action) {
                if (action instanceof Function) {
                    action();
                } else if (action === 'destroy') {
                    this.cdestroy();
                } else if (this.actionsHandler !== null) {
                    this.actionsHandler(actionOptions);
                } else {
                    this.$axios.get(action);
                }
            }
        },

        toggleFullScreen: function() {
            this.fullscreen = ! this.fullscreen;
            window.Dashmix.block('fullscreen_toggle', this.$el);
        },

        toggleContent: function() {
            this.contentShown = ! this.contentShown;
            window.Dashmix.block('content_toggle', this.$el);
        },

    }
}
