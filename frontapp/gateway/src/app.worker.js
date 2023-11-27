import Stomp from '@stomp/stompjs';

let client = null, queueName = null;
const allPorts = [], onconnect = function(e) {
    var port = e.ports[0];
    allPorts.push(port);
    port.addEventListener('message', function(e) {
        let message = e.data[0];
        if (message.type === "init" && message.queueName) {
            client = initClient(message.uri);

            if (message.queueName !== queueName) {
                client.deactivate();
            }
            queueName = message.queueName;
            connectClient(client, queueName);
            client.onStompError = function (frame) {
                console.error(frame);
            };
            client.activate();
        }
    });

    port.start();
}


function processWSMessage(msg,allPorts) {
    allPorts.forEach(port => {
        port.postMessage(msg);
    })
}

function initClient(uri) {
    return new Stomp.Client({
        brokerURL: "wss://" + uri + "/ws",
        connectHeaders: {
            login: "qore_stomp",
            passcode: "qore_stomp",
        },
        debug: function (str) {
            // console.log(str);
        },
        reconnectDelay: 5000,
        heartbeatIncoming: 4000,
        heartbeatOutgoing: 4000
    });
}

function connectClient(client, queueName) {
    client.onConnect = function (frame) {
        let callback = function (message) {
            if (message.body) {
                let msg = JSON.parse(message.body);
                if (msg.message && msg.hub) {
                    console.log(msg);
                    processWSMessage(msg,allPorts);
                }
            }
        };
        if (queueName != null) {
            client.subscribe('/amq/queue/' + queueName, callback);
        }
    }
}
