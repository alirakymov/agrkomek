import { ProtocolInterface, CommandInterface } from '_scripts/qore/protocol.js';
export default {
    mixins: [CommandInterface],

    data: function() {
        return {
            dispatchMap: [
                {key: 'title', path: 'component-title'},
                {key: 'tabs', path: 'tabs'},
            ],
            tabs: _object.get(this.options, 'tabs', {}),
        };
    },

    props: ['options'],

    mounted: function() {

    },

    computed: {
        preparedTabs: function() {
            return this.prepareTabs(this.tabs);
        }
    },

    methods: {
        commandDispatch: function(component) {
            let components = _object.get(component, 'components', this.components);
            if (components instanceof Object) {
                components = Object.values(components);
            }
            this.components = this.prepareComponents(components);
            this.tabs = _object.get(component, 'tabs', this.tabs);
        },

        prepareTabs: function(tabs) {

            var result = [], $this = this;
            if (! tabs || Object.values(tabs).length == 0) {
                return [];
            }

            _collection.each(tabs, function(tab, key){
                result.push(_object.merge(tab, {
                    id: $this.name + '-' + key,
                }));
            });

            return result;
        },

        getTabComponents: function(tabComponents) {
            let components = [];
            for (let componentName of tabComponents) {
                let index = _array.findIndex(this.components, {name: componentName});
                if (index > -1) {
                    components.push(this.components[index]);
                }
            }
            return components;
        }
    }
}
