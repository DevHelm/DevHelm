<template>
  <div class="agents-list">
    <div class="list-header">
      <h2>{{ $t('app.agents.list.title') }}</h2>
      <button class="btn--main" @click="$emit('create-agent')">
        <i class="fa-solid fa-robot"></i> {{ $t('app.agents.main.create_agent') }}
      </button>
    </div>

    <div v-if="agents.length === 0" class="empty-state">
      {{ $t('app.agents.list.empty') }}
    </div>

    <div v-else>
      <div class="agents-grid">
        <div v-for="agent in paginatedAgents" :key="agent.id" class="agent-card">
          <div class="agent-card-header">
            <h3>{{ agent.name }}</h3>
            <span class="agent-project">{{ agent.project }}</span>
          </div>
          <div class="agent-card-actions">
            <button
              class="btn--secondary btn--small"
              @click="$emit('edit-agent', agent)">
              <i class="fa-solid fa-edit"></i> Edit
            </button>
          </div>
          <div class="agent-card-footer">
            <small>{{ $t('app.agents.list.created_at') }}: {{ formatDate(agent.created_at) }}</small>
          </div>
        </div>
      </div>

      <div v-if="totalPages > 1" class="pagination">
        <div class="pagination-info">
          <label for="items-per-page">{{ $t('app.agents.list.items_per_page') }}:</label>
          <select id="items-per-page" v-model="itemsPerPage" @change="currentPage = 1">
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="50">50</option>
          </select>
        </div>

        <div class="pagination-controls">
          <button
            class="btn--secondary btn--small"
            :disabled="currentPage === 1"
            @click="currentPage--">
            {{ $t('app.agents.list.previous') }}
          </button>

          <span class="pagination-text">
            {{ $t('app.agents.list.page_info', { current: currentPage, total: totalPages }) }}
          </span>

          <button
            class="btn--secondary btn--small"
            :disabled="currentPage === totalPages"
            @click="currentPage++">
            {{ $t('app.agents.list.next') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "AgentList",
  props: {
    agents: {
      type: Array,
      required: true
    }
  },
  emits: ['create-agent', 'edit-agent'],
  data() {
    return {
      currentPage: 1,
      itemsPerPage: 10
    }
  },
  computed: {
    totalPages() {
      return Math.ceil(this.agents.length / this.itemsPerPage);
    },
    paginatedAgents() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.agents.slice(start, end);
    }
  },
  methods: {
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString();
    }
  }
}
</script>

<style scoped>
.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
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
  margin-bottom: 2rem;
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

.agent-card-actions {
  margin: 0.5rem 0;
  display: flex;
  gap: 0.5rem;
}

.btn--small {
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
}

.agent-card-footer {
  margin-top: 1rem;
  color: #666;
  font-size: 0.875rem;
}

.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 4px;
}

.pagination-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.pagination-info select {
  padding: 0.25rem 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.pagination-text {
  font-weight: 500;
}
</style>