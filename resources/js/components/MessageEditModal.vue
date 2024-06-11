<template>
  <div class="modal" v-if="isVisible">
    <div class="modal-content">
      <span class="close" @click="closeModal" role="button" aria-label="Close modal">&times;</span>
      <h2>Редактирование сообщения</h2>
      <textarea v-model="editedMessage"></textarea>
      <button @click="toggleEmojiPicker">😊</button>

      <div v-if="showEmojiPicker" class="emoji-picker-wrapper">
        <div class="emoji-picker-overlay" @click="toggleEmojiPicker"></div>
        <div class="emoji-picker">
          <Emoji @selectEmoji="insertEmoji" @close="closeEmojiPicker"/>
        </div>
      </div>

      <button @click="saveChanges">Сохранить изменения</button>
    </div>
  </div>
</template>

<script>
import Emoji from './Emoji.vue';

export default {
  components: {
    Emoji
  },
  props: ['message', 'isVisible'],
  emits: ['close', 'save'],
  data() {
    return {
      editedMessage: '',
      showEmojiPicker: false
    };
  },
  watch: {
    message: {
      immediate: true,
      handler(newValue) {
        this.editedMessage = newValue ? newValue.text : '';
      },
    },
  },
  methods: {
    closeModal() {
      this.$emit('close');
    },
    saveChanges() {
      this.$emit('save', { ...this.message, text: this.editedMessage });
      this.closeModal();
    },
    toggleEmojiPicker() {
      this.showEmojiPicker = !this.showEmojiPicker;
    },
    insertEmoji(emoji) {
      this.editedMessage += emoji.i;
    },
    closeEmojiPicker() {
      this.showEmojiPicker = false;
    }
  },
};
</script>

<style scoped>
.modal {
  font-family: Arial, sans-serif;
  display: flex;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0, 0, 0, 0.4);
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
  /* added flex-wrap */
}

.modal-content {
  background-color: #fefefe;
  margin: auto;
  padding: 20px;
  border: 1px solid #888;
  width: 50%;
  max-width: 600px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  font-family: Arial, sans-serif;
  /* added font family */
  font-size: 16px;
  /* added font size */
}

.close {
  color: #ff0000;
  position: absolute;
  top: 10px;
  right: 10px;
  font-size: 34px;
  font-weight: bold;
  transition: color 0.3s, transform 0.2s;
  cursor: pointer;
  /* added cursor pointer */
}

.close:hover,
.close:focus {
  color: black;
  text-decoration: none;
}

.close:active {
  transform: scale(0.88);
}

textarea {
  width: 100%;
  margin-top: 10px;
  padding: 10px;
  box-sizing: border-box;
  border: 1px solid #ccc;
  border-radius: 4px;
  height: 200px;
}

button {
  margin-top: 20px;
  padding: 12px 20px;
  background-color: #333;
  color: #fff;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s, transform 0.2s;
}

button:hover {
  background-color: #cccccc;
  color: #333;
}

button:active {
  background-color: #333;
  color: #fff;
  transform: scale(0.95);
}

/* Media queries */
@media (max-width: 768px) {
  .modal-content {
    width: 80%;
    max-width: none;
  }

  textarea {
    height: 150px;
  }
}

@media (max-width: 480px) {
  .modal-content {
    width: 90%;
    max-width: none;
  }

  textarea {
    height: 100px;
  }
}

.emoji-picker-wrapper {
    position: relative;
}

.emoji-picker-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0);
    z-index: 1;
}

.emoji-picker {
    position: absolute;
    top: 112px;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #fff;
    border-radius: 10px;
    padding: 10px;
    z-index: 2;
}
</style>