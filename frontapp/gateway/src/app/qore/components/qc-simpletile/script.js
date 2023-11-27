import { ProtocolInterface, CommandInterface } from '_scripts/qore/protocol.js';

export default {
    mixins: [CommandInterface],

    data: function() {
        return {};
    },

    props: ['compData'],

    mounted: function() {
    },

    computed: {
        icon: function() {
            return _object.get(this.compData, 'tileData.icon', '');
        },
        data: function() {
            return _object.get(this.compData, 'tileData.data', '');
        },
        title: function() {
            return _object.get(this.compData, 'tileData.title', '');
        },
        subtext: function() {
            return _object.get(this.compData, 'tileData.subtext', '');
        }
    },

    methods: {
    }
}
