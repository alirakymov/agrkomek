import { CommandInterface } from '_scripts/qore/protocol.js';
/** Lodash ES */
import _ from 'lodash-es';
/* Подключаем скрипт интерфейса */
import ThemeApp from '_scripts/theme/app';

export default {
    mixins: [CommandInterface],

    props: ['options'],

    data() {
        return {
            navbar: _.get(this.options, 'layout-components.navbar', null),
            navpills: _.get(this.options, 'layout-components.navpills', null),
            navpanel: _.get(this.options, 'layout-components.navpanel', null),
            stomp: _.get(this.options, 'stomp', [])
        };
    },

    mounted() {
        /** Init page theme application */
        window.Dashmix = new ThemeApp();
        console.log(this.components)
    },

    updated() {
        /** Reinit page theme application */
        this.initTheme();
    },

    methods: {
        componentInBlock: function(component) {
            return _.get(component, 'data.inBlock', false);
        },

        componentActionIcon: function(action) {
            return _.get(action, 'icon', 'fa fa-cog');
        },

        componentActionClick: function(action, e) {
            e.preventDefault();
            this.$axios.get(action.actionUri);
        },

        initTheme() {
            window.Dashmix.helpers([ 'one-ripple', 'bs-tooltip', 'bs-popover', ]);
        },

    }
}
