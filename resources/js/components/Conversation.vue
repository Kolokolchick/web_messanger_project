<template>
    <div class="conversation">
        <header>
            <h1>{{ contact? contact.name + ' (' + contact.email + ')' : group? group.name : 'Выберите диалог' }}</h1>
        </header>
            <MessagesFeed 
                v-if="contact"
                :contact="contact" 
                :messages="sortedMessages"
                :user="this.user"
                @edit-message="editMessageHandler" 
                @delete-message="deleteMessage"
            />
            <MessagesFeed 
                v-else-if="group"
                :group="group" 
                :messages="sortedMessages" 
                :user="this.user"
                @edit-message="editMessageHandlerForGroup" 
                @delete-message="deleteMessageInGroup"
            />
            <MessageComposer 
                v-if="contact"
                @send="sendMessage"
            />
            <MessageComposer 
                v-else-if="group"
                @send="sendMessageToGroup"
            />
    </div>
</template>

<script>
import MessagesFeed from './MessagesFeed.vue';
import MessageComposer from './MessageComposer.vue';

export default {
    props: {
        contact: {
            type: Object,
            default: null
        },
        group: {
            type: Object,
            default: null
        },
        user: {
            type: Object,
            default: null
        },
        messages: {
            type: Array,
            default: []
        }
    },

    components: 
    {
        MessagesFeed, 
        MessageComposer
    },

    methods: {
        sendMessage(text) {
            if (!this.contact) {
                return;
            }

            axios.post('/conversation/send', {
                contact_id: this.contact.id,
                text: text
            }).then((response) => {
                this.addMessage(response.data);
                this.$emit('new-contact'); // Событие для обновления списка контактов
            }).catch(error => {
                console.error('Error sending message:', error);
                alert('Error: ' + error.response.data.error);
            })
        },
        sendMessageToGroup(text) {
            if (!this.group) {
                return;
            }

            axios.post('/conversation/group/send', {
                group_id: this.group.id,
                text: text,
            }).then((response) => {
                this.addMessage(response.data);
            }).catch(error => {
                console.error('Error sending message:', error);
                alert('Error: ' + error.response.data.error);
            })
        },

        addMessage(message) {
            this.messages.push(message);
            console.log(message);
        },

        editMessageHandler(message) {
            const newText = message.text;
            if (newText) {
                this.editMessage(message.id, newText);
            }
        },

        editMessage(messageId, newText) {
            axios.patch(`/conversation/message/${messageId}/edit`, {
                text: newText
            }).then((response) => {
                const index = this.messages.findIndex(m => m.id === messageId);
                if (index !== -1) {
                    this.messages[index] = response.data;
                }
            }).catch(error => {
                console.error("Error editing message:", error);
            });
        },

        editMessageHandlerForGroup(message) {
            const newText = message.text;
            if (newText) {
                this.editMessageForGroup(message.id, newText);
            }
        },

        editMessageForGroup(messageId, newText) {
            console.log(messageId); 
            axios.patch(`/conversation/group/message/${messageId}/edit`, {
                text: newText
            }).then((response) => {
                const index = this.messages.findIndex(m => m.id === messageId);
                if (index !== -1) {
                    this.messages[index] = response.data;
                }
            }).catch(error => {
                console.error("Error editing message:", error);
            });
        },

        deleteMessage(messageId) {
            axios.delete(`/conversation/message/${messageId}/delete`)
            .then(() => {
                const index = this.messages.findIndex(m => m.id === messageId);
                if (index !== -1) {
                    this.messages.splice(index, 1);
                }
            }).catch(error => {
                console.error("Error deleting message:", error);
            });
        },

        deleteMessageInGroup(messageId) {
            axios.delete(`/conversation/group/message/${messageId}/delete`)
            .then(() => {
                const index = this.messages.findIndex(m => m.id === messageId);
                if (index !== -1) {
                    this.messages.splice(index, 1);
                }
            }).catch(error => {
                console.error("Error deleting message:", error);
            });
        }
    },

    computed: {
        sortedMessages() {
            return this.messages.sort((a, b) => {
                return new Date(a.created_at) - new Date(b.created_at);
            });
        }
    }
}
</script>

<style lang="scss" scoped>
.conversation {
    flex: 3;
    background: #f7f7f7;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
    width: 100%;

    @media (max-width: 768px) {
        height: 100%; 
        width: 100%;
    }

    header {
        background: #fff;
        padding: 1rem;
        border-bottom: 1px solid #ddd; 

        @media (max-width: 768px)  {
            position: fixed;
            top: 0;
            width: 100%;
            left: -2%;
            margin-top: 60px;   
        }
    }

    h1 {
        font-size: 1.75rem; 
        margin: 0; 
        color: #333; 
        font-weight: bold; 
    }

   .messages-feed {
        padding: 1rem;
    }
}
</style>