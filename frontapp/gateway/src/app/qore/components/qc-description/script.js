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
        columns: function() {
            return this.compData.columns ? this.compData.columns : [];
        },

        model: function() {
            return this.compData.descriptionData ? this.compData.descriptionData : {};
        }
    },

    methods: {

        getHeaderClass: function(column) {
            return column['class-header'] ? column['class-header'] : '';
        },

        getColumnClass: function(column) {
            return column['class-column'] ? column['class-column'] : '';
        }
    }
}
