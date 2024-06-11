<template>
    <div class="modal-content">
        <h3>Добавить пользователя в группу</h3>
        <form @submit.prevent="addUserToGroup">
            <div class="form-group">
                <label for="user-select">Выберите пользователя:</label>
                <select v-model="selectedUserId" id="user-select">
                    <option v-for="user in users" :key="user.id" :value="user.id">
                        {{ user.name }}
                    </option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Добавить</button>
        </form>
    </div>
</template>
  
<script>
export default {
    props: ['groupId'],

    data() {
        return {
            users: [], // Список всех пользователей для выбора
            selectedUserId: null, // ID выбранного пользователя
        };
    },

    mounted() {
        this.fetchUsers();
    },

    methods: {
        fetchUsers() {
            axios.get('/contacts') // Используем API для получения контактов
                .then(response => {
                    this.users = Object.values(response.data); // Преобразуем объект в массив
                })
                .catch(error => {
                    alert('Error: ' + error.response.data.message);
                    console.error('Error fetching users:', error);
                });
        },

        addUserToGroup() {
            axios.post(`/groups/${this.groupId}/add-user`, { user_id: this.selectedUserId })
                .then(() => {
                    this.$emit('close');
                    this.$emit('userAdded');
                })
                .catch(error => {
                    console.error('Error adding user to group:', error);
                    alert('Error: ' + error.response.data.message);
                });
        }
    }
}
</script>

<style scoped>
    .modal-content {
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

    .form-group {
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-bottom: 10px;
    }

    select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
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