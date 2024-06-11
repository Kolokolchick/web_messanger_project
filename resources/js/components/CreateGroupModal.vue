<template>
    <div>
        <h2> Создать новую группу </h2>
        <input v-model="groupName" placeholder="Укажите название группы">
        <button @click="createGroup"> Создать </button>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                groupName: ''
            };
        },

        methods: {
            createGroup() {
                axios.post('/groups/create', { name: this.groupName })
                    .then(response => {
                        this.$emit('close');
                    })
                    .catch(error => {
                        alert('Error creating group!');
                    });
            },
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

    h2 {
        margin-top: 0;
    }

    input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    button {
        background-color: #333;
        color: #fff;
        border: none;
        border-radius: 4px;
        padding: 10px 20px;
        cursor: pointer;
    }

    button:hover {
        background-color: #222;
    }

    button:active {
        background-color: #000;
    }
</style>