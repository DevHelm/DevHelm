<template>
  <div class="form-container">
    <h2>{{ $t('app.agents.create.title') }}</h2>
    <form @submit.prevent="createAgent">
      <div class="form-group">
        <label for="agent-name">{{ $t('app.agents.create.name') }}</label>
        <input
          id="agent-name"
          v-model="agent.name"
          type="text"
          class="form-control"
          :class="{'form-control--error': errors.name}"
          required
        />
        <div v-if="errors.name" class="error-message">{{ errors.name }}</div>
      </div>

      <div class="form-group">
        <label for="agent-project">{{ $t('app.agents.create.project') }}</label>
        <input
          id="agent-project"
          v-model="agent.project"
          type="text"
          class="form-control"
          :class="{'form-control--error': errors.project}"
          maxlength="10"
          required
        />
        <div v-if="errors.project" class="error-message">{{ errors.project }}</div>
      </div>

      <div v-if="errors.general" class="error-message general-error">
        {{ errors.general }}
      </div>

      <div class="form-actions">
        <SubmitButton :in-progress="isCreating" :loading-text="$t('app.agents.create.creating')">
          {{ $t('app.agents.create.submit') }}
        </SubmitButton>
        <button type="button" class="btn--secondary" @click="cancel">
          {{ $t('app.agents.create.cancel') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: "AgentCreate",
  emits: ['agent-created', 'cancel'],
  data() {
    return {
      isCreating: false,
      agent: {
        name: '',
        project: ''
      },
      errors: {}
    }
  },
  methods: {
    async createAgent() {
      this.isCreating = true;
      this.errors = {};

      try {
        const response = await axios.post('/app/agents', this.agent);
        this.$emit('agent-created', response.data);
        this.resetForm();
      } catch (error) {
        if (error.response && error.response.data.errors) {
          this.errors = error.response.data.errors;
        } else if (error.response && error.response.data.error) {
          this.errors.general = error.response.data.error;
        } else {
          this.errors.general = 'An error occurred while creating the agent';
        }
      } finally {
        this.isCreating = false;
      }
    },
    cancel() {
      this.resetForm();
      this.$emit('cancel');
    },
    resetForm() {
      this.agent = {
        name: '',
        project: ''
      };
      this.errors = {};
    }
  }
}
</script>

<style scoped>
.form-container {
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 2rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.form-control {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 1rem;
}

.form-control--error {
  border-color: #dc3545;
}

.error-message {
  color: #dc3545;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.general-error {
  margin-bottom: 1rem;
  padding: 0.75rem;
  background: #f8d7da;
  border: 1px solid #f5c2c7;
  border-radius: 4px;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
}
</style>