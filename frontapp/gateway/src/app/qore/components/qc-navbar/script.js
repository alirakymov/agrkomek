import { CommandInterface } from '_scripts/qore/protocol.js';
/** Vue loader */
import { defineAsyncComponent } from 'vue';
/** Lodash ES */
import _ from 'lodash-es';

export default {
    mixins: [CommandInterface],
    props: ['options'],
    data: function() {
        return {
            name: 'navbar',
        };
    },
    mounted: function() {
    },
    computed: {
        qnElements() {
            var qfElements = [],
                iteration = 0;

            if (typeof(this.options) === 'undefined') {
                return [];
            }

            _.each(this.options, function(data){
                let uniqKey = 'level-' + iteration;
                if (typeof(data.sublevel) !== 'undefined') {
                    qfElements.push({
                        data: {
                            label: data.label,
                            name: 'navitem-' + uniqKey,
                            key: uniqKey,
                        },
                        type: 'qn-navheader',
                        id: uniqKey,
                    });
                    let subIterator = 0;
                    _.each(data.sublevel, function(subData){
                        var subUniqKey = uniqKey + '-' + subIterator;
                        subData.key = subUniqKey;
                        subData.name = 'navitem-' + subUniqKey;
                        qfElements.push({
                            data: subData,
                            type: 'qn-navitem',
                            id: subUniqKey,
                            name: 'navitem-' + subUniqKey,
                        });
                        subIterator++;
                    });
                } else {
                    data.name = 'navitem-' + uniqKey;
                    qfElements.push({
                        data: data,
                        type: 'qn-navitem',
                        id: uniqKey,
                        name: 'navitem-' + uniqKey,
                    });
                }
                iteration++;
            });

            return qfElements;
        }

    }
}

export const initializer = (app) => {
    for (let component of ['qn-navheader', 'qn-navitem']) {
        app.component(component, require(['.', 'qn-elements', component, 'component'].join('/') + '.vue').default);
    }
}
