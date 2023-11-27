import { ProtocolInterface, CommandInterface } from '_scripts/qore/protocol.js';
/** Lodash ES */
import _ from 'lodash-es';
import QctTab from './qct-tab/component.vue';

export default {
    mixins: [CommandInterface],
    components: {
        'qct-tab': QctTab,
    },

    data: function() {
        return {
            dispatchMap: [],
            indents: _.get(this.options, 'indents', true),
            activeComponent: null,
        };
    },

    updated() {
    },

    props: ['options'],

    mounted: function() {

        if (this.components.length) {
            this.activeComponent = this.components[0].name;
        }

        for (let component of this.components) {
            if (_.get(component, 'data.active', false)) {
                this.activeComponent = component.name;
            }
        }
    },

    computed: {
    },

    methods: {
        commandDispatch(data) {
        },
    }
}
