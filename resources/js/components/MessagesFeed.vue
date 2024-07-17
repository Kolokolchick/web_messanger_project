<template>
    <div class="feed" ref="feed">
        <ul v-if="contact">
            <li v-for="message in messages" 
                :class="`message${message.to === contact.id ? ' sent' : ' received'}`" 
                :key="message.id" 
                :scrollToBottom="scrollToBottom(messages)"
            > 
                <div>
                    <div v-if="message.from === this.user.id">
                        <span class="name">You</span>
                    </div>
                    <div v-else>
                        <span class="name">{{ contact.name }}</span>
                    </div>
                    <div class="text">
                        {{ message.text }}
                        <div class="timestamp">
                            {{ formatTime(message.created_at) }}
                        </div>
                        <div class="message-actions" v-if="message.to === contact.id">
                            <button @click="openEditModal(message)" class="edit-button">Редактировать</button>
                            <button @click="emitDeleteEvent(message.id)" class="delete-button">Удалить</button>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
        <ul v-else-if="group">
            <li v-for="(message, index) in messages" 
                :class="`message${message.from === this.user.id ? ' sent' : ' received'}`" 
                :key="message.id" 
                :scrollToBottom="scrollToBottom(messages)"
            >
                <div>
                    <div v-if="message.from === this.user.id">
                        <span class="name">You</span>
                    </div>
                    <div v-else>
                        <span class="name">{{ message.from_name }}</span>
                    </div>
                    <div class="text">
                        {{ message.text }}
                        <div class="timestamp">
                            {{ formatTime(message.created_at) }}
                        </div>
                        <div class="message-actions" v-if="message.from === this.user.id">
                            <button @click="openEditModal(message)" class="edit-button">Редактировать</button>
                            <button @click="emitDeleteEvent(message.id)" class="delete-button">Удалить</button>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
        <MessageEditModal 
            :isVisible="isModalVisible" 
            :message="currentMessage" 
            @close="isModalVisible = false" 
            @save="handleSave"
        />
    </div>
</template>

<script>
import MessageEditModal from './MessageEditModal.vue';

export default {
    components: {
        MessageEditModal,
    },
    props: {
        contact: {
            type: Object
        },
        group: {
            type: Object
        },
        user: {
            type: Object
        },
        messages: {
            type: Array,
            required: true
        }
    },
    data() {
        return {
            isModalVisible: false,
            currentMessage: null,
            previousSender: null,
            previousSenderName: null
        };
    },
    methods: {
        scrollToBottom() {
            this.$nextTick(() => {
                setTimeout(() => {
                    if (this.$refs.feed) {
                        this.$refs.feed.scrollTo({
                            top: this.$refs.feed.scrollHeight - this.$refs.feed.clientHeight,
                            behavior: 'smooth'
                        });
                    }
                }, 50);
            });
        },
        openEditModal(message) {
            this.currentMessage = message;
            this.isModalVisible = true;
        },
        emitEditEvent(message) {
            this.$emit('edit-message', message);
        },
        emitDeleteEvent(messageId) {
            this.$emit('delete-message', messageId);
        },
        handleSave(updatedMessage) {
            this.emitEditEvent(updatedMessage);
            this.isModalVisible = false;
        },
        formatTime(datetime) {
            const date = new Date(datetime);
            const formattedDate = date.toLocaleDateString('ru-RU', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            const formattedTime = date.toLocaleTimeString('ru-RU', {
                hour: '2-digit',
                minute: '2-digit'
            });
            return `${formattedDate} | ${formattedTime}`;
        }
    }
}
</script>

<style lang="scss" scoped>
.feed {
    background: #f0f0f0;
    height: 800px;
    max-height: 800px;
    overflow-y: auto; 
    font-family: Arial, sans-serif;
    font-size: 16px;

    ul {
        list-style-type: none;
        padding: 10px; 
        margin-left: 20px;

        li {
            &.message {
                display: flex;
                margin: 15px 0;
                width: 100%;

                .name {
                    font-weight: bold;
                    color: #333; 
                    float: left; 
                    margin-right: 10px; 
                }

                .text {
                    max-width: 600px; 
                    border-radius: 10px;
                    padding: 15px;
                    display: inline-block;
                    white-space: pre-wrap;
                    word-wrap: break-word; 
                    overflow-wrap: break-word; 
                    overflow: hidden; 
                    text-overflow: ellipsis; 
                    margin: 10px; 
                    color: #333;
                    background-color: #f7f7f7;
                    text-align: left;
                    margin-left: 2px; 

                    @media (max-width: 768px) { 
                        max-width: 260px;
                    }
                }

                &.received {
                    .text {
                        background-color: #e5e5e5;
                        border-top-left-radius: 0;
                    }
                }

                &.sent {
                    .text {
                        background-color: #ccc;
                        border-top-left-radius: 0;
                    }
                }
            }
        }
    }
}

.message-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 10px; 

    button {
        background: none;
        border: none;
        border-radius: 5px;
        padding: 0;
        cursor: pointer;
        margin-left: 10px; 
        font-size: 16px; 
        color: #333;
        transition: color 0.3s;
        transition: background-color 0.3s;

        &:hover {
            background-color: #007bff;
            color: #fff;
        }

        &:focus {
            outline: none;
        }
    }

    button.edit-button {
        color: #333;
        background-color: #f0f0f0;
        border: none;
        border-radius: 5px;
        padding: 5px 10px;
        font-size: 14px;
        cursor: pointer;

        &:hover {
            background-color: #333;
            color: #e5e5e5;
        }

        &:active {
            background-color: #fff;
            color: #333;
        }
    }

    button.delete-button {
        color: #a00;
        background-color: #f0f0f0;
        border: none;
        border-radius: 5px;
        padding: 5px 10px;
        font-size: 14px;
        cursor: pointer;

        &:hover {
            background-color: #800;
            color: #e5e5e5;
        }

        &:active {
            background-color: #fff;
            color: #800;
        }
    }
}

.timestamp {
    font-size: 14px; 
    color: #666; 
    margin: 10px;    
}


@media (max-width: 768px) {
    .feed {
        height: 100%;
        max-height: 100%;
        padding-top: 40px;
        padding-bottom: 40px;
    }

    .message-actions {
        margin-top: 5px; 
    }
}
</style>