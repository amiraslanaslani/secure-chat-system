import http from 'k6/http';
import { check, sleep } from 'k6';

let usersNumber = 300

export let options = {
    stages: [
        { duration: '30s', target: usersNumber },
        { duration: '1m', target: usersNumber },
        { duration: '30s', target: 0 },
    ],
    thresholds: {
        http_req_duration: ['p(95)<300'], // 95% of requests should be below 500ms
        http_req_failed: ['rate<0.001'],   // error rate should be less than 1%
    },
};

const BASE_URL = 'http://localhost/chat/project/apis/';
const channels = ['test1', 'test2', 'test3', 'test4', 'test5'];

// Track last read message index for each VU
let lastRead = new Array(__ENV.K6_VUS || 10).fill(0);

export default function () {
    const vu = __VU - 1; // __VU is 1-based
    // 1. Send a message
    let sendPayload = {
        name: `user${__VU}`,
        message: `Hello from user${__VU} at ${Date.now()}`,
        channel: channels[vu % channels.length]
    };
    let sendRes = http.post(`${BASE_URL}chat/send`, sendPayload);

    check(sendRes, {
        'send status is 200': (r) => r.status === 200,
        'send success': (r) => r.json('success') === true,
    });

    // 2. Read messages from last read index
    for (let i = 0; i < 3; i++) { 
        let from = lastRead[vu];
        let readRes = http.get(`${BASE_URL}chat/read?channel=general&from=${from}`);
        check(readRes, {
            'read status is 200': (r) => r.status === 200,
            'read returns array': (r) => Array.isArray(r.json()),
        });
        let messages = readRes.json();
        if (Array.isArray(messages) && messages.length > 0) {
            lastRead[vu] += messages.length;
        }
        sleep(1);
    }
}
