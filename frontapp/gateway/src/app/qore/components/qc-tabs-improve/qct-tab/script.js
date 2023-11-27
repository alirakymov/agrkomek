import { ProtocolInterface, CommandInterface } from '_scripts/qore/protocol.js';

export default {

    mixins: [CommandInterface],

    data: function() {
        return {
            dispatchMap: [],
            mergeOnDispatch: [],
        };
    },

    props: ['options'],

    mounted: function() {
    },

    computed: {
    },

    methods: {
    }
}
