<template>
    <div class="composer">
        <textarea v-model="message" @keydown="handleKeyDown" placeholder="Message..."></textarea>
        <button @click="toggleEmojiPicker">
            😊
        </button>
        <div v-if="showEmojiPicker" class="emoji-picker-wrapper">
            <div class="emoji-picker-overlay" @click="toggleEmojiPicker"></div>
            <div class="emoji-picker">
                <Emoji @selectEmoji="insertEmoji" @close="closeEmojiPicker"/>
            </div>
        </div>
        <button @click="send">
            Отправить
        </button>
    </div>
</template>


<script>
import Emoji from './Emoji.vue';

export default {
    components: {
        Emoji
    },
    data() {
        return {
            message: '',
            showEmojiPicker: false
        };
    },
    methods: {
        send() {
            if (this.message.trim() === '') {
                return;
            }

            this.$emit('send', this.message.trim());
            this.message = '';
        },
        handleKeyDown(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                this.send();
            }
        },
        toggleEmojiPicker() {
            this.showEmojiPicker = !this.showEmojiPicker;
        },
        insertEmoji(emoji) {
            this.message += emoji.i;
        },
        closeEmojiPicker() {
            this.showEmojiPicker = false;
        }
    }
}
</script>


<style lang="scss" scoped>
.composer {
    font-family: Arial, sans-serif;
    display: flex;
    align-items: center;
    margin: 10px;
    padding: 10px;
    background-color: #f7f7f7;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);

    @media (max-width: 768px) {
        position: fixed;
        bottom: 0;
        width: 100%;
        left: -1%;
        margin: 0;
    }
}

.composer textarea {
    flex: 1;
    resize: none;
    border-radius: 3px;
    border: 1px solid #ddd;
    padding: 10px;
    font-size: 14px;
    overflow-y: hidden;
    min-height: 40px;
    background-color: #fff;
    transition: border-color 0.3s;

    &:focus {
        border-color: #aaa;
    }
}

.composer button {
    margin-left: 10px;
    padding: 8px 12px;
    border-radius: 4px;
    background-color: #333;
    color: #fff;
    border: none;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.2s;

    &:hover {
        background-color: #444;
    }

    &:active {
        transform: scale(0.95);
    }

    @media (max-width: 768px) {
        margin: 10px 0;
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
    background-color: #29292986;

}

.emoji-picker {
    position: fixed;  // Фиксированное позиционирование
    top: 50%;         // Сдвиг вниз на 50% высоты экрана
    left: 50%;        // Сдвиг вправо на 50% ширины экрана
    transform: translate(-50%, -50%);  // Центрирование по обеим осям
    background-color: #fff;
    border-radius: 10px;
    padding: 10px;
    z-index: 2;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);

    @media (max-width: 768px) {
        // Убедитесь, что все свойства для адаптивного дизайна применены
        top: 50%; 
        left: 50%;
        transform: translate(-50%, -50%);
    }
}
</style>