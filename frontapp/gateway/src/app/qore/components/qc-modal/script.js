import { ProtocolInterface, CommandInterface } from '_scripts/qore/protocol.js';
/** Lodash ES */
import _ from 'lodash-es';

export default {
    mixins: [CommandInterface],

    data: function() {
        return {
            title: _.get(this.options, 'title', ''),
            panel: _.get(this.options, 'panel', {}),
            show: _.get(this.options, 'show', false),
            type: _.get(this.options, 'modal-type', null),
            size: _.get(this.options, 'size', 'lg'),
        };
    },

    props: ['options'],

    beforeMount: function() {
    },

    mounted: function() {
        this.$root.modals.push(this);
        
    },

    beforeDestroy() {
        let index = _.findIndex(this.$root.modals, {name: this.name});
        if (index > -1) {
            this.$root.modals.splice(index, 1);
        }
    },

    computed: {
        typeStyle() {
            switch(true) {
                case this.type == 'rightside':
                    return 'modal-dialog-rightside';
                default:
                    return 'modal-dialog-popin';
            }
        },
        sizeStyle() {
            switch(true) {
                case this.size == 'xl':
                    return 'modal-xl';
                case this.size == 'lg':
                    return 'modal-lg';
                case this.size == 'sm':
                    return 'modal-sm';
                default:
                    return '';
            }
        }
    },

    methods: {
        commandClose: function() {
            this.$root.modalPop();
            this.show = false;
        },

        commandOpen: function() {
            this.show = true;
        },

        reaction(button, e) {
            let actions = button.action;
            if (! actions) {
                return;
            }

            if (typeof actions === 'string') {
                CommandInterface.reaction({
                    actionUri: actions,
                }, e);
                return;
            }

            e.preventDefault();

            if (typeof actions === 'object') {
                actions = [actions];
            }

            for (let action of actions) {
                let componentName =  action.component ? action.component : this.name;
                this.$protocol.executeCommands(componentName, [action]);
            }
        },

        destroy: function() {
            ProtocolInterface.executeCommands(
                this.$parent.name,
                [
                    {
                        command: 'destroyComponent',
                        options: { name: this.name }
                    }
                ]
            );
        },
    }
}
