<template>
  <LoadingScreen :ready="!isLoading" :loading-message="$t('app.agents.list.loading')">
    <h1 class="page-title">{{ $t('app.agents.main.title') }}</h1>

    <AgentCreate
      v-if="showCreateForm"
      @agent-created="handleAgentCreated"
      @cancel="showCreateForm = false"
    />

    <AgentEdit
      v-if="showEditForm"
      :agent-data="selectedAgent"
      @agent-updated="handleAgentUpdated"
      @cancel="showEditForm = false"
    />

    <AgentList
      :agents="agents"
      @create-agent="showCreateForm = true"
      @edit-agent="handleEditAgent"
    />
  </LoadingScreen>
</template>

<script>
import axios from 'axios';
import AgentList from '../../components/app/Agent/AgentList.vue';
import AgentCreate from '../../components/app/Agent/AgentCreate.vue';
import AgentEdit from '../../components/app/Agent/AgentEdit.vue';

export default {
  name: "Agents",
  components: {
    AgentList,
    AgentCreate,
    AgentEdit
  },
  data() {
    return {
      showCreateForm: false,
      showEditForm: false,
      isLoading: false,
      agents: [],
      selectedAgent: null
    }
  },
  mounted() {
    this.loadAgents();
  },
  methods: {
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
    handleAgentCreated(newAgent) {
      this.agents.push(newAgent);
      this.showCreateForm = false;
    },
    handleEditAgent(agent) {
      this.selectedAgent = agent;
      this.showEditForm = true;
      this.showCreateForm = false;
    },
    handleAgentUpdated(updatedAgent) {
      const agentIndex = this.agents.findIndex(a => a.id === updatedAgent.id);
      if (agentIndex !== -1) {
        this.$set(this.agents, agentIndex, updatedAgent);
      }
      this.showEditForm = false;
      this.selectedAgent = null;
    }
  }
}
</script>

<style scoped>
/* Main page styles remain here if needed */
</style>