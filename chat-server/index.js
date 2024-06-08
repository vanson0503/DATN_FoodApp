const express = require('express');
const app = express();
const http = require('http');
const server = http.createServer(app);
const { Server } = require('socket.io');
const cors = require('cors');

app.use(cors({
    origin: 'https://vanson.io.vn',
    methods: ["GET", "POST"]
}));

const io = new Server(server, {
    cors: {
        origin: "https://vanson.io.vn",
        methods: ["GET", "POST"],
        credentials: true
    }
});

app.get('/', (req, res) => {
    res.sendFile(__dirname + '/index.html');
});

io.on('connection', (socket) => {
    console.log('User connected: ' + socket.id);
    var room;

    socket.on('join', async (data) => {
        room = data;
        socket.join(data);
        console.log(`User ${socket.id} joined room ${data}`);

        try {
            const fetch = (await import('node-fetch')).default;
            const response = await fetch(`https://vanson.io.vn/food-api/public/api/customer/${room}/messages`);
            const data = await response.json();
            socket.emit('history', data);
        } catch (error) {
            console.error('Error fetching messages:', error);
        }
    });

    socket.on('message', async (message) => {
        console.log(`Message sent to room ${room}: ${message}`);
        io.emit('has-message',true);
        io.to(room).emit('thread', message);
        const data = JSON.parse(message);

        try {
            const fetch = (await import('node-fetch')).default;
            const response = await fetch('https://vanson.io.vn/food-api/public/api/messages', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    sender_id: data.sender_id,
                    receiver_id: data.receiver_id,
                    content: data.content,
                    sender_type: data.sender_type
                })
            });
            const responseData = await response.json();
            console.log('Message saved:', responseData);
        } catch (error) {
            console.error('Error saving message:', error);
        }
    });
});

server.listen(8888, () => {
    console.log("Listening on port 8888");
});
