<template>
    <div>
        <h3>Исключить пользователя из группы</h3>
        <label>Выберите пользователя:</label>
        <form @submit.prevent="removeUserFromGroup">
            <select v-model="selectedUserId">
                <option v-for="user in groupUsers" :key="user.id" :value="user.id">
                    {{ user.name }}
                </option>
            </select>
            <button type="submit">Исключить</button>
        </form>
    </div>
</template>
  
<script>
  export default {
    props: ['groupId'],

    data() {
      return {
        groupUsers: [], // Список пользователей в группе для выбора
        selectedUserId: null, // ID выбранного пользователя
      };
    },

    mounted() {
      this.fetchGroupUsers();
    },

    methods: {
        fetchGroupUsers() {
            axios.get(`/groups/${this.groupId}/users`)
                .then(response => {
                  this.groupUsers = response.data;
                })
                .catch(error => {
                  alert('Error: ' + error.response.data.message);
                  console.error('Error fetching group users:', error);
                });
        },

        removeUserFromGroup() {
            axios.delete(`/groups/${this.groupId}/remove-user`, { data: { user_id: this.selectedUserId } })
            .then(() => {
                this.$emit('close');
                this.$emit('userRemoved');
            })
            .catch(error => {
                console.error('Error removing user from group:', error);
                alert('Error: ' + error.response.data.message);
            });
        }
    }
  }
</script>

<style scoped>
    div {
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
        border: 1px solid #ccc;
        padding: 20px;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    h3 {
        margin-top: 0;
    }

    select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    button[type="submit"] {
        background-color: #333;
        color: #fff;
        border: none;
        border-radius: 4px;
        padding: 10px 20px;
        cursor: pointer;
    }

    button[type="submit"]:hover {
        background-color: #222;
    }

    button[type="submit"]:active {
        background-color: #000;
    }
</style>