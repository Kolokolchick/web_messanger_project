<template>
    <div class="chat-app">
        <button @click="showSidebar =!showSidebar; showContacts = false; showGroups = false" class="toggle-sidebar-button" :class="{ open: showSidebar }">Меню</button>
        <aside v-if="showSidebar" class="sidebar" :style="{ transform: showSidebar? 'translateX(0)' : 'translateX(-100%)' }">
            <button @click="showSidebar =!showSidebar; showContacts = false; showGroups = false" class="sidebar-button">Закрыть меню</button>
            <button @click="showContacts =!showContacts; showGroups = false; showCreateGroupModal = false" class="sidebar-button">Контакты</button>
            <button @click="showGroups =!showGroups; showContacts = false; showCreateGroupModal = false; showGroupList = true" class="sidebar-button">Группы</button>
            <hr>
            <div v-if="showContacts">
                <ContactsList 
                    :contacts="contacts"
                    @selected="startConversationWith" 
                    @contactRemoved="handleContactRemoved" 
                />
            </div>
            <div v-if="showGroups">
                <button @click="showCreateGroupModal =!showCreateGroupModal; showGroupList = !showGroupList" class="sidebar-button">Создать группу</button>
                <div v-if="showCreateGroupModal">
                    <CreateGroupModal
                        @close="showCreateGroupModal = false; showGroupList = true"
                    />
                </div>
                    <GroupList 
                        ref="groupListref"
                        v-if="showGroupList"
                        :user="user" 
                        @selected="startConversationWithGroup"
                        @sendUser = "startConversationWith"
                    />
            </div>
        </aside>
        
        <Conversation 
            v-if="selectedContact"
            :contact="selectedContact" 
            :messages="messages"
            :user="user"  
            @new="saveNewMessage" 
            @new-contact="handleNewContact" 
            :update-contacts-method="updateContacts"
        />
        <Conversation 
            v-else-if="selectedGroup"
            :group="selectedGroup" 
            :messages="messages"
            :user = "this.user"
        />
        <Conversation 
            v-else
        />
    </div>
</template>

<script>
    import { useToast } from "vue-toastification";
    import Conversation from './Conversation.vue';
    import ContactsList from './ContactsList.vue';
    import CreateGroupModal from './CreateGroupModal.vue';
    import GroupList from './GroupList.vue';

    export default {

        components: {
            Conversation, 
            ContactsList, 
            CreateGroupModal, 
            GroupList,
        },

        props: {
            user: {
                type: Object,
                required: true
            },
        },

        data() {
            return {
                selectedContact: null,
                selectedGroup: null,
                messages: [],
                contacts: [],
                showCreateGroupModal: false,
                view: 'contacts',
                showSidebar: false,
                showContacts: false,
                showGroups: false,
                showGroupList: true,
                groupChannels: [],
            };
        },

        mounted() {
            const toast = useToast();

            Echo.private(`messages.${this.user.id}`)
                .listen('NewMessage', (e) => {
                    this.handleIncoming(e.message);
                });  
            Echo.private(`contacts.${this.user.id}`)
                .listen('NewContact', (e) => {
                    const exists = this.contacts.find(contact => contact.id === e.contact.id);
                    if (!exists) {
                        e.contact.unread = 0;
                        this.contacts.push(e.contact);
                    }
                });
            Echo.private(`chat.${this.user.id}`)
                .listen('MessageEdited', (e) => {
                    this.handleEditedMessage(e.message);
                });
            Echo.private(`chat.${this.user.id}`)
                .listen('MessageDeleted', (e) => {
                    this.handleDeletedMessage(e.messageId);
                });
            Echo.private(`UserInGroup.${this.user.id}`)
                .listen('AddUserInGroup', (e) => {
                    if (this.user.id === e.AddedUserId) {
                        if (this.showSidebar && this.showGroups) {
                            this.$nextTick(() => {
                                if (this.$refs.groupListref) {
                                    this.$refs.groupListref.fetchGroups();
                                }
                            });
                        }
                        toast.success(`Вы были добавлены в группу "${e.GroupName}" !`);
                    }
                });
            Echo.private(`UserInGroup.${this.user.id}`)
                .listen('RemoveUserInGroup', (e) => {
                    if (this.user.id === e.RemovedUserId) {
                        if (this.$refs.groupListref) {
                            if (this.selectedGroup !== null && this.selectedGroup.id === e.GroupId) {
                                this.$refs.groupListref.selectGroup(null);
                            }
                        } else {
                            if (this.selectedGroup !== null && this.selectedGroup.id === e.GroupId) {
                                this.startConversationWithGroup(null);
                            }
                        }
                        if (this.showSidebar && this.showGroups) {
                            if (this.$refs.groupListref) {
                                this.$refs.groupListref.fetchGroups();
                            }
                        }
                        toast.error(`Вы были удалены из группы "${e.GroupName}" !`);
                    }
                });

            axios.get('/contacts')
                .then((response) => {
                    this.contacts = response.data;
                });
        },

        methods: {
            updateContacts() {
                axios.get('/contacts')
                    .then((response) => {
                        this.contacts = response.data;
                    });
            },
            handleNewContact() {
                this.updateContacts();
            },
            startConversationWith(contact) {
                axios.get(`/conversation/${contact.id}`)
                    .then((response) => {
                        this.messages = response.data;
                        this.selectedContact = contact;
                        this.selectedGroup = null;
                        this.updateUnreadCount(contact.id, true);
                        this.showSidebar = false;
                        //this.showContacts = false;
                        //this.showGroups = false;
                    });
            },
            startConversationWithGroup(group) {
                if (group == null) {
                    this.selectedGroup = null;
                    return;
                }
                this.unsubscribeFromGroupChannels();
                
                axios.get(`/conversation/group/${group.id}`)
                    .then((response) => {
                        this.messages = response.data;
                        this.selectedGroup = group;
                        this.selectedContact = null;
                    });

                const newGroupChannels = [
                    Echo.channel(`NewMessageGroup.${group.id}`)
                        .listen('NewMessageGroup', (e) => {
                            this.handleIncomingGroup(e.message);
                        }),
                    
                    Echo.channel(`MessageEditedGroup.${group.id}`)
                        .listen('MessageEditedGroup', (e) => {
                            this.handleEditedMessage(e.message);
                        }),

                    Echo.channel(`MessageDeletedGroup.${group.id}`)
                        .listen('MessageDeletedGroup', (e) => {
                            this.handleDeletedMessage(e.messageId);
                        })
                ];

                this.groupChannels = newGroupChannels;
            },
            unsubscribeFromGroupChannels() {
                this.groupChannels.forEach(channel => {
                    channel.stopListening('NewMessageGroup');
                    channel.stopListening('MessageEditedGroup');
                    channel.stopListening('MessageDeletedGroup');
                    Echo.leaveChannel(channel.name);
                });
                this.groupChannels = [];
            },
            saveNewMessage(message) {
                this.messages.push(message);
            },
            handleIncoming(message) {
                if (this.selectedContact && message.from === this.selectedContact.id) {
                    this.saveNewMessage(message);
                    axios.post(`/conversation/${message.from}/read`);
                    return;
                }
                this.updateUnreadCount(message.from, false);
            },
            handleIncomingGroup(message) {
                if (this.selectedGroup && message.group_id === this.selectedGroup.id) {
                    this.saveNewMessage(message);
                    return;
                }
            },
            updateUnreadCount(senderId, reset) {
                this.contacts = this.contacts.map((contact) => {
                    if (contact.id === senderId) {
                        if (reset) {
                            contact.unread = 0;
                        } else {
                            contact.unread += 1;
                        }
                    }
                    return contact;
                });
            },
            handleEditedMessage(message) {
                const index = this.messages.findIndex(msg => msg.id === message.id);
                if (index !== -1) {
                    this.messages[index] = message;
                }
            },
            handleDeletedMessage(messageId) {
                this.messages = this.messages.filter(msg => msg.id !== messageId);
            },
            handleContactRemoved(contactId) {
                this.contacts = this.contacts.filter(contact => contact.id !== contactId);
                this.selectedContact = null;
            }
        }
    }
</script>

<style lang="scss" scoped>
.chat-app {
    font-family: Arial, sans-serif;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    width: auto;
    height: 100%;
    max-width: 1200px;
    margin: 0 auto;
    margin-top: -80px;
    
    @media (max-width: 768px) {
        flex-direction: column;
        align-items: center;
    }

    .sidebar {
        position: fixed;
        top: 5%;
        left: 0;
        width: 100%;
        max-width: 400px;
        height: 100%;
        background-color: #f7f7f7;
        border-right: 1px solid #ddd;
        padding: 20px;
        transform: translateX(-100%);
        transition: transform 0.3s ease-in-out;
        overflow-y: auto;

        @media (max-width: 768px) {
            width: 100%;
            max-width: 100%;
            top: 6%;
            z-index: 1;
        }
    }

   .sidebar button {
        display: block;
        width: 100%;
        //margin-bottom: 10px;
    }

   .sidebar div {
        padding: 20px;
    }

   .toggle-sidebar-button {
        position: fixed;
        top: 50%;
        left: 18px;
        background-color: #333;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: transform 0.15s ease-in-out;
        z-index: 3;

        &:hover {
            background-color: #222;
        }

        &:active {
            background-color: #000;
        }

        &.open {
            transform: translateX(340px) rotate(90deg) translateY(-50%);
        }

        @media (max-width: 768px) {
            top: 5px;
            left: 40%;
            background-color: #fff;
            color: #333;
            border: 1px solid #bebdbd;

            &:hover {
                background-color: #333;
            }

            &:active {
                background-color: #fff;
            }

            &.open {
                transform: translateX(0) rotate(0) translateY(5%);
            }
        }
    }

    .sidebar button.sidebar-button {
        display: block;
        width: 100%;
        margin-bottom: 10px;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        background-color: #333;
        color: #fff;
        cursor: pointer;
        transition: background-color 0.2s ease-in-out;
    }
    
    .sidebar button.sidebar-button:hover {
        background-color: #222;
    }
    
    .sidebar button.sidebar-button:active {
        background-color: #000;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    }
}
</style>