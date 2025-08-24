<template>
  <LoadingScreen :ready="!isLoading" :loading-message="$t('app.agents.list.loading')">
    <h1 class="page-title">{{ $t('app.agents.main.title') }}</h1>

    <div class="top-button-container">
      <button class="btn--main" :class="{'btn--main--disabled': showCreateForm}"  @click="toggleCreateForm" :disabled="showCreateForm">
        <i class="fa-solid fa-robot"></i> {{ $t('app.agents.main.create_agent') }}
      </button>
    </div>

    <div v-if="showCreateForm" class="form-container">
      <h2>{{ $t('app.agents.create.title') }}</h2>
      <form @submit.prevent="createAgent">
        <div class="form-group">
          <label for="agent-name">{{ $t('app.agents.create.name') }}</label>
          <input 
            id="agent-name"
            v-model="newAgent.name" 
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
            v-model="newAgent.project" 
            type="text" 
            class="form-control"
            :class="{'form-control--error': errors.project}"
            maxlength="10"
            required
          />
          <div v-if="errors.project" class="error-message">{{ errors.project }}</div>
        </div>

        <div class="form-actions">
          <SubmitButton :in-progress="isCreating" :loading-text="$t('app.agents.create.creating')">
            {{ $t('app.agents.create.submit') }}
          </SubmitButton>
          <button type="button" class="btn--secondary" @click="cancelCreate">
            {{ $t('app.agents.create.cancel') }}
          </button>
        </div>
      </form>
    </div>

    <div class="agents-list">
      <h2>{{ $t('app.agents.list.title') }}</h2>
      <div v-if="agents.length === 0" class="empty-state">
        {{ $t('app.agents.list.empty') }}
      </div>
      <div v-else class="agents-grid">
        <div v-for="agent in agents" :key="agent.id" class="agent-card">
          <div class="agent-card-header">
            <h3>{{ agent.name }}</h3>
            <span class="agent-project">{{ agent.project }}</span>
          </div>
          <div class="agent-card-footer">
            <small>{{ $t('app.agents.list.created_at') }}: {{ formatDate(agent.created_at) }}</small>
          </div>
        </div>
      </div>
    </div>
  </LoadingScreen>
</template>

<script>
import axios from 'axios';

export default {
  name: "Agents",
  data() {
    return {
      showCreateForm: false,
      isCreating: false,
      isLoading: false,
      agents: [],
      newAgent: {
        name: '',
        project: ''
      },
      errors: {}
    }
  },
  mounted() {
    this.loadAgents();
  },
  methods: {
    toggleCreateForm() {
      this.showCreateForm = !this.showCreateForm;
      if (this.showCreateForm) {
        this.resetForm();
      }
    },
    cancelCreate() {
      this.showCreateForm = false;
      this.resetForm();
    },
    resetForm() {
      this.newAgent = {
        name: '',
        project: ''
      };
      this.errors = {};
    },
    async createAgent() {
      this.isCreating = true;
      this.errors = {};

      try {
        const response = await axios.post('/app/agents', this.newAgent);
        this.agents.push(response.data);
        this.showCreateForm = false;
        this.resetForm();
        // Show success message (could integrate with notification system)
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
    async loadAgents() {
      this.isLoading = true;
      try {
        const response = await axios.get('/app/agents');
        this.agents = response.data;
      } catch (error) {
        console.error('Error loading agents:', error);
      } finally {
        this.isLoading = false;
      }
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString();
    }
  }
}
</script>

<style scoped>
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

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
}

.agents-list {
  margin-top: 2rem;
}

.empty-state {
  text-align: center;
  padding: 2rem;
  color: #666;
}

.agents-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.agent-card {
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.agent-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.agent-card-header h3 {
  margin: 0;
  color: #333;
}

.agent-project {
  background: #007bff;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.875rem;
}

.agent-card-footer {
  margin-top: 1rem;
  color: #666;
  font-size: 0.875rem;
}
</style>