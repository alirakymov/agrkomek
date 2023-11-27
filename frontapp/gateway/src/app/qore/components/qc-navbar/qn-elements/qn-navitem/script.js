import { ProtocolInterface, CommandInterface } from '_scripts/qore/protocol.js';
/** Lodash ES */
import _ from 'lodash-es';

export default {
    mixins: [CommandInterface],
    data: function() {
        return {
            name: _.get(this.itemData, 'id', 'navitem'),
        };
    },
    props: ['itemData'],
    mounted: function() {
    },
    computed: {

        route: function() {
            return typeof(this.itemData.route) !== 'undefined' ? this.itemData.route : '#';
        },

        sublevel: function() {
            return typeof(this.itemData.sublevel) !== 'undefined' ? this.itemData.sublevel : false;
        },

        icon: function() {
            return (typeof(this.itemData.icon) !== 'undefined' ? this.itemData.icon : 'si si-doc');
        },

        subItems: function() {

            var
                subItems = [],
                iteration = 0,
                self = this;

            _.each(this.itemData.sublevel, function(data){

                var uniqKey = data.key + '-' + iteration;

                subItems.push({
                    data: _.merge({icon: self.icon, key: uniqKey}, data),
                    type: 'qn-navitem',
                    id: uniqKey
                });

                iteration++;
            });

            return subItems;
        }
    }
}
