<template>
  <div class="form-container">
    <h2>{{ $t('app.agents.edit.title') }}</h2>
    <form @submit.prevent="updateAgent">
      <div class="form-group">
        <label for="edit-agent-name">{{ $t('app.agents.edit.name') }}</label>
        <input
          id="edit-agent-name"
          v-model="agent.name"
          type="text"
          class="form-control"
          :class="{'form-control--error': errors.name}"
          required
        />
        <div v-if="errors.name" class="error-message">{{ errors.name }}</div>
      </div>

      <div class="form-group">
        <label for="edit-agent-project">{{ $t('app.agents.edit.project') }}</label>
        <input
          id="edit-agent-project"
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
        <SubmitButton :in-progress="isUpdating" :loading-text="$t('app.agents.edit.updating')">
          {{ $t('app.agents.edit.submit') }}
        </SubmitButton>
        <button type="button" class="btn--secondary" @click="cancel">
          {{ $t('app.agents.edit.cancel') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: "AgentEdit",
  props: {
    agentData: {
      type: Object,
      required: true
    }
  },
  emits: ['agent-updated', 'cancel'],
  data() {
    return {
      isUpdating: false,
      agent: {
        id: '',
        name: '',
        project: ''
      },
      errors: {}
    }
  },
  watch: {
    agentData: {
      immediate: true,
      handler(newAgent) {
        if (newAgent) {
          this.agent = {
            id: newAgent.id,
            name: newAgent.name,
            project: newAgent.project
          };
          this.errors = {};
        }
      }
    }
  },
  methods: {
    async updateAgent() {
      this.isUpdating = true;
      this.errors = {};

      try {
        const response = await axios.post(`/app/agent/${this.agent.id}/edit`, {
          name: this.agent.name,
          project: this.agent.project
        });

        this.$emit('agent-updated', response.data);
        this.resetForm();
      } catch (error) {
        if (error.response && error.response.data.errors) {
          this.errors = error.response.data.errors;
        } else if (error.response && error.response.data.error) {
          this.errors.general = error.response.data.error;
        } else {
          this.errors.general = 'An error occurred while updating the agent';
        }
      } finally {
        this.isUpdating = false;
      }
    },
    cancel() {
      this.resetForm();
      this.$emit('cancel');
    },
    resetForm() {
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