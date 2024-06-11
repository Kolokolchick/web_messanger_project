<template>
    <div class="user-list-container">
        <h2>Пользователи группы</h2>
        <ul>
            <li v-for="user in userList" :key="user.id">
                <div class="user-info">
                    <p>{{ user.name }}</p>
                    <p>{{ user.email }}</p>
                </div>
                <button @click="sendUser(user)">Написать</button>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    emits: ['sendUser'],
    props: ['groupId'],
    data() {
        return {
            userList: []
        };
    },
    mounted() {
        axios.get(`/groups/${this.groupId}/users`)
                .then((response) => {
                    this.userList = response.data;
                })
                .catch(error => {
                    alert('Error: ' + error.response.data.message);
                    console.error('Error fetching users:', error);
                });
    },
    methods: {
        sendUser(user) {
            this.$emit('sendUser', user);
        }
    }
}
</script>

<style lang="scss" scoped>
    .user-list-container {
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
        border: 1px solid #ccc;
        padding: 20px;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    h2 {
        color: #333;
        font-size: 24px;
        margin-bottom: 20px;
    }

    ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
    }

    li {
        border-bottom: 1px solid #ccc;
        padding: 10px 0;
    }

    .user-info {
        margin-bottom: 10px;
    }

    p {
        margin: 0;
    }

    p:first-child {
        font-weight: bold;
    }

    p + p {
        color: #666;
    }

    button {
        background-color: #333;
        color: #fff;
        border: none;
        border-radius: 4px;
        padding: 5px 10px;
        cursor: pointer;
        transition: background-color 0.3s;
        font-size: 14px;
    }

    button:hover {
        background-color: #222;
    }

    button:active {
        background-color: #000;
    }
</style>
