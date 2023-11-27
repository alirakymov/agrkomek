import { CommandInterface } from '_scripts/qore/protocol.js';
/** Lodash ES */
import _ from 'lodash-es';

export default {
    mixins: [CommandInterface],

    data: function() {
        return {
            dialogShow: _.get(this.options, 'show', false),
            actions: _.get(this.options, 'actions', []),
        };
    },

    props: ['options'],

    beforeMount: function() {
    },

    mounted: function() {
        var vue = this;
        document.addEventListener("keydown", function(e) {
            if (vue.dialogShow && e.keyCode == 27) {
                vue.commandClose();
            }
        });
    },

    computed: {
        dialogActions: {
            get: function() {
                return this.actions instanceof Object
                    ? Object.values(this.actions)
                    : this.actions;
            },
            set: function(actions) {
                this.actions = actions instanceof Object
                    ? Object.values(actions)
                    : actions;
            }
        }
    },

    methods: {
        commandClose: function() {
            this.dialogShow = false;
        },

        commandOpen: function() {
            this.dialogShow = true;
        },

        show: function(options) {
            let components = _.get(options, 'components', []);
            /** prepare message component */
            let message = _.get(options, 'message', false);
            if (message) {
                components.push({
                    name: this.name + '-alert',
                    type: 'qc-infoblock',
                    data: _.get(message, 'message', ''),
                    'info-type': _.get(message, 'type', 'info'),
                    'component-title': _.get(message, 'title', null),
                });
            }
            /** set components */
            this.components = this.prepareComponents(components);
            this.actions = _.get(options, 'actions', this.actions);

            this.dialogShow = true;
        },

        close: function() {
            this.commandClose();
        },

        getActionType: function(action) {
            let type = _.get(action, 'type', 'primary');
            switch (type) {
                case 'primary':
                    return 'btn-alt-primary';
                case 'secondary':
                    return 'btn-alt-secondary';
                case 'success':
                    return 'btn-alt-success';
                case 'warning':
                    return 'btn-alt-warning';
                case 'danger':
                    return 'btn-alt-danger';
                case 'info':
                    return 'btn-alt-info';
            }
        },

        evalAction: function(action) {
            if (typeof action === 'string') {
                if (action === 'close') {
                    this.dialogShow = false;
                } else {
                    this.$axios.get(action);
                    this.dialogShow = false;
                }
            }else if(action instanceof Function) {
                action(this);
            }
        },
    }

}
