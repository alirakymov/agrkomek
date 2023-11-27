import { ProtocolInterface, CommandInterface } from '_scripts/qore/protocol.js';

export default {
    mixins: [CommandInterface],
    props: ['options']
}
