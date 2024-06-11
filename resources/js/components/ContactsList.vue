<template>
    <div class="contacts-list">
        <div class="search">
            <input type="text" v-model="searchTerm" @input="handleSearch" placeholder="Search contacts...">
        </div>

        <h2>Сохранённые контакты</h2>
        <br>
        <ul>
            <li v-for="contact in contacts" :key="'my-' + contact.id" class="contact-item" :class="{ 'selected': contact === selected }" @click="selectContact(contact)">
                <div class="contact-info">
                    <!--
                    <div class="avatar">
                        <img :src="contact.profile_image" :alt="contact.name">
                    </div>
                    -->
                    <div class="contact-details">
                        <p class="name">{{ contact.name }}</p>
                        <p class="email">{{ contact.email }}</p>
                    </div>
                    <span class="unread" v-if="contact.unread">{{ contact.unread }}</span>
                </div>
                <div class="contact-actions">
                    <button v-if="!contact.confirmDelete" @click.stop="confirmDelete(contact)" class="remove-button">Delete</button>
                    <button v-else @click.stop="cancelDelete(contact)" class="remove-button">No</button>
                    <button v-if="contact.confirmDelete" @click.stop="removeContact(contact.id)" class="remove-button">Yes</button>
                </div>
            </li>
        </ul>

        <hr>
        <br>

        <h2 v-if="searchResults.length > 0">Результаты поиска</h2>
        <br>
        <ul v-if="searchResults.length > 0">
            <li v-for="contact in searchResults" :key="'search-' + contact.id" class="contact-item" :class="{ 'selected': contact === selected }" @click="selectContact(contact)">
                <div class="contact-info">
                    <!--
                    <div class="avatar">
                        <img :src="contact.profile_image" :alt="contact.name">
                    </div>
                    -->
                    <div class="contact-details">
                        <p class="name">{{ contact.name }}</p>
                        <p class="email">{{ contact.email }}</p>
                    </div>
                    <span class="unread" v-if="contact.unread">{{ contact.unread }}</span>
                </div>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    props: {
        contacts: {
            type: Array,
            default: () => []
        }
    },

    data() {
        return {
            selected: null,
            searchTerm: '',
            searchResults: [],
            searchDebounce: null, // Для учета debounce-таймера
        };
    },

    methods: {
        selectContact(contact) {
            this.selected = contact;
            this.$emit('selected', contact);
        },

        handleSearch() {
            // Отменяем предыдущие debounce, если они были установлены
            if (this.searchDebounce) {
                clearTimeout(this.searchDebounce);
            }

            // Проверяем, является ли значение в поле поиска пустым
            if (this.searchTerm.trim() === '') {
                // Если значение пустое, очищаем результаты поиска
                this.searchResults = [];
                return; // Выходим из метода, чтобы предотвратить отправку запроса на сервер
            }

            // Устанавливаем новый debounce
            this.searchDebounce = setTimeout(() => {
                this.searchContacts();
            }, 300); // 300 мс задержка
        },

        searchContacts() {
            // Теперь этот метод будет вызван при каждом срабатывании debounce
            axios.post('/contacts/search', { query: this.searchTerm })
                .then(response => {
                    this.searchResults = response.data;
                })
                .catch(error => {
                    console.error('Ошибка поиска контактов:', error);
                });
        },

        confirmDelete(contact) {
            contact.confirmDelete = true;
        },

        cancelDelete(contact) {
            contact.confirmDelete = false;
        },

        removeContact(contactId) {
            // Удаление контакта
            axios.delete(`/contacts/${contactId}`)
                .then(() => {
                    // Вместо изменения props напрямую, отправляем событие вверх к родителю
                    this.$emit('contactRemoved', contactId);
                })
                .catch(error => {
                    console.error('Ошибка удаления контакта:', error);
                });
        },
    },
    
    watch: {
        // Этот наблюдатель отследит изменения searchTerm и в случае их появления вызовет метод handleSearch
        searchTerm(newVal, oldVal) {
            if (newVal !== oldVal) {
                this.handleSearch();
            }
        }
    }
};
</script>

<style scoped>
/* Global styles */
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  line-height: 1.6;
  color: #333;
  background-color: #f5f5f5;
}

/* Contacts list */
.contacts-list {
  font-family: Arial, sans-serif;
  max-width: 400px;
  margin: 2rem auto;
  padding: 1rem;
  background-color: #f5f5f5;
  border-radius: 4px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  overflow-y: auto;
}

/* Search */
.search {
  margin-bottom: 1rem;
}

.search input[type="text"] {
  width: 100%;
  padding: 8px;
  font-size: 16px;
  border: 1px solid #ccc;
  border-radius: 4px;
  background-color: #fff;
  color: #333;
}

/* Contact item */
.contact-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 15px;
  background-color: #fff;
  border-radius: 4px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  margin-bottom: 10px;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.3s ease;
}

.contact-item:hover {
  background-color: #f7f7f7;
  transform: translateY(-2px);
}

.contact-item.selected {
  background-color: #d1d1d1;
  color: #fff;
}

/* Contact info */
.contact-info {
  display: flex;
  align-items: center;
  flex-grow: 1;
}

.contact-details {
  flex-grow: 1;
}

.name {
  font-size: 16px;
  font-weight: bold;
  margin-bottom: 4px;
  color: #333;
}

.email {
  font-size: 14px;
  color: #333;
}

.unread {
  background-color: #333;
  color: #fff;
  padding: 4px 8px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: bold;
  margin-left: 1rem;
}

/* Contact actions */
.contact-actions {
  display: flex;
  align-items: center;
}

.remove-button {
  background-color: #ff5252;
  border: none;
  color: #fff;
  font-size: 14px;
  cursor: pointer;
  margin-left: 1rem;
  padding: 4px 8px;
  border-radius: 4px;
  transition: background-color 0.3s ease;
}

.remove-button:hover {
  background-color: #ff3737;
}

.remove-button:active {
  background-color: #fc1717;
}

/* Media queries */
@media (max-width: 768px) {
 .contacts-list {
    max-width: 300px;
  }
}

@media (max-width: 480px) {
 .contacts-list {
    max-width: 100%;
  }
}
</style>