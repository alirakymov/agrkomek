import { CommandInterface } from '_scripts/qore/protocol.js';
/** Lodash ES */
import _ from 'lodash-es';

export default {
    mixins: [CommandInterface],
    data: function () {
        return {
            queueName: _.get(this.options, 'stomp.queueName', ''),
            uri: _.get(this.options, 'stomp.uri', ''),
            port: '',
            worker: new SharedWorker('/static-gateway/assets/js/socket.share.bundle.js'),
        };
    },

    props: ['options'],

    mounted: function () {
        let protocol = this.$protocol;
        this.worker.port.start();
        this.worker.port.addEventListener("message", (e) => {
            protocol.process({data: e.data.message});
        });
        this.worker.port.postMessage([{
            type: "init",
            uri: this.uri,
            queueName: this.queueName
        }])
    },
    methods: {
        commandDispatch: function (component) {
        },
    }
}
