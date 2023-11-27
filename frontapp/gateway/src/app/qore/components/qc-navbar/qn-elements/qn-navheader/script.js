import { ProtocolInterface, CommandInterface } from '_scripts/qore/protocol.js';
/** Lodash ES */
import _ from 'lodash-es';
export default {
    mixins: [CommandInterface],
    data: function() {
        return {
            name: _.get(this.itemData, 'id', 'navheader'),
        };
    },
    props: ['itemData'],
    mounted: function() {
    },
    computed: {
    }
}
