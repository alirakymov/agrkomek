import { ProtocolInterface, CommandInterface } from '_scripts/qore/protocol.js';
/** Lodash ES */
import _ from 'lodash-es';

export default {
    mixins: [CommandInterface],
    props: [ 'tile' ],

    data: function() {
        return {};
    },

    mounted: function() {
    },

    computed: {
        thumb: function() {
            return  _.get(this.tile, 'content.source.thumb', '');
        },
        title: function() {
            return  _.get(this.tile, 'content.source.data.name', '');
        }
    }
}
