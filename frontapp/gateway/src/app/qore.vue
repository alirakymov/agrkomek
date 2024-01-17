<script>
/** Interface Gateway */
import { ProtocolInterface, CommandInterface } from './qore/protocol.js';
import _ from 'lodash-es';

if (typeof(appData) === 'undefined') {
    let appData = {};
}

export default {
    mixins: [CommandInterface],
    data() {
        return {
            name: 'qore-app',
            themeApp: null,
            modals: [],
            mounted: false,
            scriptYandexMap: null,
        };
    },

    beforeCreate: function () {
        /** Start protocol */
        ProtocolInterface.start(this);
        /** Set start application structure from backend-template array */
        ProtocolInterface.process({ data: appData });
    },

    created: function () {
        /** Init protocol */
        this.initProtocol();
    },

    watch: {
        modals: {
            handler(newModals, oldModals) {
                if (newModals.length > 0) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = 'auto';
                }
            },
            deep: true,
        }
    },

    mounted() {
        /** Register event listener for modals list*/
        let $this = this;
        document.addEventListener("keydown", function(e) {
            if (e.keyCode == 27 && $this.modals.length > 0) {
                let modal = $this.modals.pop();
                modal.commandClose();
            }
        });

        // Установить скрипты для использования яндекс карты
        // this.scriptYandexMap = document.createElement('script');
        // this.scriptYandexMap.setAttribute('src', 'https://api-maps.yandex.ru/2.1/?apikey=6438dbae-14c9-4e50-8df4-9672951f5190&lang=ru_RU&mode=debug');
        // document.head.appendChild(this.scriptYandexMap);
    },

    updated() {
        /* this.$protocol.flush(); */
    },

    methods: {
        /** Initialize app protocol */
        initProtocol: function () {
            /** Add a request interceptor to axios */
            this.$axios.interceptors.request.use((config) => {
                return _.merge(config, {headers: this.$protocol.getRequestHeaders()});
            }, function (error) {
                return console.log(error);
            });

            /** Add a response interceptor to axios */
            this.$axios.interceptors.response.use((response) => {
                this.$protocol.process(response);
                return response;
            }, function (error) {
                return console.log(error);
            });

            /** Init protocol component dispatcher */
            this.$protocol.init(this);
        },

        modalPop() {
            this.modals.pop();
        },

        request: function (link) {
            this.$axios.get(link);
        },
    }
}

</script>
<template>
    <component v-for="component in components"
        :is="component.type"
        :key="component.id"
        :options="component.data"
        ref="children"
        @cdestroy="cdestroy()"
    />
</template>
