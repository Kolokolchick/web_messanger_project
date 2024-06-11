<template>
    <div>
      <h3>Удалить группу</h3>
      <p>Вы уверены что хотитие удалить выбранную группу?</p>
      <button @click="deleteGroup">Удалить группу</button>
    </div>
</template>
  
  <script>
  export default {
    props: ['groupId'],

    methods: {
      deleteGroup() {
        axios.delete(`/groups/${this.groupId}`)
          .then(() => {
            this.$emit('close');
            this.$emit('groupDeleted');
          })
          .catch(error => {
            alert('Error: ' + error.response.data.message);
            console.error('Error deleting group:', error);
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

p {
    margin-bottom: 20px;
}

button {
    background-color: #ff5252;
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 10px 20px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #ff3737;
}

button:active {
    background-color: #fc1717;
}
</style>