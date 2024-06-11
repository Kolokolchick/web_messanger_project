<template>
    <div class="groups-list-container">
      <div class="groups-list">
        <h2>Доступные группы</h2>
        <ul>
          <transition-group name="group" tag="ul">
            <li v-for="group in groups" :key="group.id" :class="{ 'selected': group.id === selectedGroupId }" @click="selectGroup(group)">
              <p>{{ group.name }}</p>
            </li>
          </transition-group>
        </ul>
      </div>
  
      <transition-group name="group" tag="ul">
        <div class="actions" v-if="selectedGroupId">
          <button v-if="isGroupOwner" @click="showAddUserModal = !showAddUserModal; showRemoveUserModal = false; showDeleteGroupModal = false; showUserList = false">Добавить пользователя в группу</button>
          <button v-if="isGroupOwner" @click="showRemoveUserModal = !showRemoveUserModal; showAddUserModal = false; showDeleteGroupModal = false; showUserList = false">Исключить пользователя из группы</button>
          <button v-if="isGroupOwner" @click="showDeleteGroupModal = !showDeleteGroupModal; showAddUserModal = false; showRemoveUserModal = false; showUserList = false">Удалить группу</button>
          <button v-if="isGroupOwner" @click="showUserList = !showUserList; showAddUserModal = false; showRemoveUserModal = false; showDeleteGroupModal=false">Список пользователей группы</button>
          <button v-if="!isGroupOwner" @click="leaveGroup(selectedGroupId)">Покинуть группу</button>
          <button v-if="!isGroupOwner" @click="showUserList = !showUserList">Список пользователей группы</button>

        </div>
      </transition-group>
  
      <AddUserToGroupModal 
        v-if="showAddUserModal" 
        :groupId="selectedGroupId"
        @userAdded="onUserAdded"
        @close="showAddUserModal = false"
      />
      <RemoveUserFromGroupModal
        v-if="showRemoveUserModal"
        :groupId="selectedGroupId"
        @userRemoved="onUserRemoved"
        @close="showRemoveUserModal = false"
      />
      <DeleteGroupModal
        v-if="showDeleteGroupModal"
        :groupId="selectedGroupId"
        @groupDeleted="onGroupDeleted(selectedGroupId)"
        @close="showDeleteGroupModal = false"
      />
      <GroupUserList
        v-if="showUserList"
        :groupId="selectedGroupId"
        @sendUser="sendUser"
      />
    </div>
  </template>

<script>
    import AddUserToGroupModal from './AddUserToGroupModal.vue';
    import RemoveUserFromGroupModal from './RemoveUserFromGroupModal.vue';
    import DeleteGroupModal from './DeleteGroupModal.vue';
    import GroupUserList from './GroupUserList.vue';

    export default {
        components: {
            AddUserToGroupModal,
            RemoveUserFromGroupModal,
            DeleteGroupModal,
            GroupUserList
        },

        props: {
            user: {
                type: Object,
                required: true
            }
        },

        emits: ['selected', 'sendUser'],

        data() {
            return {
                groups: [],
                selectedGroupId: null, // ID выбранной группы
                showAddUserModal: false, // Управление видимостью модального окна
                showRemoveUserModal: false, // Управление видимостью модального окна
                showDeleteGroupModal: false, // Управление видимостью модального окна
                showUserList: false
            };
        },

        computed: {
          isGroupOwner() {
              if (this.selectedGroupId) {
                  const group = this.groups.find(group => group.id === this.selectedGroupId);
                  if (group && typeof group === 'object' && group !== null && 'created_by' in group) {
                      return group.created_by === this.user.id;
                  } else {
                      console.warn("Group not found or invalid structure for selectedGroupId:", this.selectedGroupId);
                  }
              }
              return false;
          }
      },

        created() {
            this.fetchGroups();
        },

        methods: {
            selectGroup(group) {
              if (group != null) {
                this.selectedGroupId = group.id;
                this.$emit('selected', group);
              } else {
                this.selectedGroupId = null;
                this.$emit('selected', null);
              }
            },

            leaveGroup (groupId) {
                axios.delete(`/groups/${this.selectedGroupId}/leave`, { data: { user_id: this.user.id } })
                .then(() => {
                    this.groups = this.groups.filter(group => group.id !== groupId);
                    this.selectGroup(null);
                })
                .catch(error => {
                    alert('Error: ' + error.response.data.message);
                    console.error('Error leaving group:', error);
                });
            },

            fetchGroups() {
                axios.get('/groups')
                    .then(response => {
                        this.groups = response.data;
                    })
                    .catch(error => {
                        alert('Error: ' + error.response.data.message);
                        console.error('Error fetching groups', error);
                    });
            },

            onGroupDeleted(groupId) {
                this.showDeleteGroupModal = false; // Закрыть модальное окно после удаления группы
                this.groups = this.groups.filter(group => group.id !== groupId);
                this.selectGroup(null);
            },

            onUserRemoved() {
                this.showRemoveUserModal = false; // Закрыть модальное окно после удаления пользователя
                // Обновить список пользователей в группе или другие данные
            },
            
            onUserAdded() {
                this.showAddUserModal = false; // Закрыть модальное окно после добавления пользователя
                // Обновить список пользователей в группе или другие данные
            },

            clearGroupList (groupId) {
              this.groups = this.groups.filter(group => group.id !== groupId);
            },
            sendUser (user) {
              this.$emit('sendUser', user);
            }
        }
    }
</script>

<style scoped>
.groups-list-container {
   font-family: Arial, sans-serif;
   display: flex;
   flex-wrap: wrap;
   justify-content: center;
 }

.groups-list {
   flex-basis: 300px;
   margin: 20px;
   background: #f5f5f5;
   border-radius: 4px;
   padding: 20px;
   box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
   max-height: 600px;
   overflow-y: auto;
 }

 h2 {
   font-size: 20px;
   margin-top: 10px;
   color: #333;
 }

 ul {
   list-style-type: none;
   padding: 0;
 }

 li {
   margin-bottom: 10px;
   padding: 15px;
   background-color: #fff;
   border-radius: 4px;
   box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
   transition: background-color 0.3s ease, transform 0.3s ease;
   cursor: pointer;
 }

 li:hover {
   background-color: #ebebeb;
   transform: translateY(-2px);
 }

 li p {
   margin: 0;
   font-size: 18px;
   font-weight: bold;
   color: #333;
 }

.group-enter-active,
.group-leave-active {
   transition: opacity 0.3s ease, transform 0.3s ease;
 }

.group-enter-from,
.group-leave-to {
   opacity: 0;
   transform: translateX(-20px);
 }

.actions {
   flex-basis: 300px;
   margin: 20px;
   display: flex;
   flex-wrap: wrap;
   justify-content: center;
 }

 button {
   margin: 10px;
   padding: 8px 16px;
   background-color: #333;
   color: #fff;
   border: none;
   border-radius: 4px;
   cursor: pointer;
   transition: background-color 0.3s ease;
 }

 button:hover {
   background-color: #222;
 }

 button:active {
   background-color: #000;
 }

 li.selected {
    background-color: #d1d1d1;
    color: #fff;
 }

 li.selected p {
   color: #333;
 }

 /* Add some responsive design tweaks */
 @media (max-width: 768px) {
  .groups-list-container {
     flex-direction: column;
   }
  .groups-list {
     flex-basis: 100%;
     margin: 10px;
   }
  .actions {
     flex-basis: 100%;
     margin: 10px;
   }
 }
</style>