<template>
	<NcModal v-if="isModalOpen"
		id="unified-search"
		:name="t('core', 'Conversations')"
		:show.sync="isModalOpen"
		:size="'small'"
		:clear-view-delay="0"
		:title="t('core', 'Conversations')"
		@close="closeModal">
		<!-- Conversations -->
		<div class="unified-search-conversations-list-modal">
			<h1>{{ t('core', 'Select a conversation') }}</h1>
			<div class="unified-search-conversations-list-modal__header">
				<NcTextField :value.sync="searchText"
					trailing-button-icon="close"
					class="search-form"
					:label="t('spreed', 'Search conversations')"
					:show-trailing-button="searchText !== ''"
					@update:value="debouncedFilterConversations"
					@trailing-button-click="clearText">
					<Magnify :size="16" />
				</NcTextField>
			</div>
			<div class="unified-search-conversations-list-modal__body">
				<div id="room-list">
					<ul v-if="!loading && availableConversations.length > 0">
						<NcListItem v-for="room in availableConversations"
							:key="room.id"
							:name="room.displayName"
							:bold="false"
							:active="room.token === selectedConversation.token"
							:details="'1hr'"
							:counter-number="room.unreadMessages"
							counter-type="highlighted"
							@click="select(room)">
							<template #icon>
								<ConversationIcon :item="room" :hide-favorite="false" />
							</template>
							<template #subname>
								{{ room.lastMessage.message }}
							</template>
						</NcListItem>
					</ul>
					<div v-else-if="!loading" class="no-match-message">
						<h2 class="no-match-title">
							{{ noMatchFoundTitle }}
						</h2>
						<p v-if="noMatchFoundSubtitle" class="subtitle">
							{{ noMatchFoundSubtitle }}
						</p>
					</div>
				</div>
			</div>
			<div class="unified-search-conversations-list-modal__footer">
				<NcButton v-if="!loading && availableConversations.length > 0"
					type="primary"
					:disabled="!selectedConversation.token"
					@click="searchInConversation">
					{{ t('core', 'Select conversation') }}
				</NcButton>
			</div>
		</div>
	</NcModal>
</template>

<script>
import debounce from 'debounce'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcModal from '@nextcloud/vue/dist/Components/NcModal.js'
import NcListItem from '@nextcloud/vue/dist/Components/NcListItem.js'
import NcTextField from '@nextcloud/vue/dist/Components/NcTextField.js'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import { searchListedConversations, fetchConversations } from '../../services/TalkService.js'
import ConversationIcon from '../UnifiedSearch/ConversationIcon.vue'

export default {
	name: 'ConversationsListModal',
	components: {
		ConversationIcon,
		NcButton,
		NcModal,
		NcListItem,
		NcTextField,
		Magnify,
	},
	props: {
		isOpen: {
			type: Boolean,
			required: true,
		},
	},
	data() {
		return {
			availableConversations: [],
			searchText: '',
			selectedConversation: {},
			loading: false,
			debouncedFilterConversations: debounce(this.filterConversations, 300),
		}
	},
	computed: {
		isModalOpen: {
			get() {
				return this.isOpen
			},
			set(value) {
				this.$emit('update:is-open', value)
			},
		},
		noMatchFoundTitle() {
			return t('core', 'No conversations on this instance')
		},
		noMatchFoundSubtitle() {
			return t('core', 'Conversations list is empty')
		},
	},
	beforeMount() {
		this.fetchRooms()
		// const $store = OCA.Talk?.instance?.$store
		// if ($store) {
		// 	this.currentRoom = $store.getters.getToken()
		// }
	},
	methods: {
		clearText() {
			this.searchText = ''
		},
		filterConversations() {
			this.fetchRooms()
		},
		sortConversations(conversation1, conversation2) {
			if (conversation1.isFavorite !== conversation2.isFavorite) {
				return conversation1.isFavorite ? -1 : 1
			}

			return conversation2.lastActivity - conversation1.lastActivity
		},
		async fetchRooms() {
			const response = this.listOpenConversations
				? await searchListedConversations({ searchText: this.searchText }, {})
				: await fetchConversations({})

			this.availableConversations = response.data.ocs.data.sort(this.sortConversations)
			console.debug('Available conversations', this.availableConversations)
			this.loading = false
		},
		select(room) {
			this.selectedConversation = room
		},
		closeModal() {
			this.isModalOpen = false
		},
		searchInConversation() {
			this.$emit('search-in:conversation', this.selectedConversation)
			this.closeModal()
		},
	},
}
</script>

<style lang="scss" scoped>
.unified-search-conversations-list-modal {
	padding: 10px 20px 10px 20px;
	min-height: 60vh;

	.subtitle {
		color: var(--color-text-maxcontrast);
		margin-bottom: 8px;
	}

	.search-form {
		margin-bottom: 10px;
	}

	h1 {
		font-size: 16px;
		font-weight: bolder;
		line-height: 2em;
	}

	&__header {
		display: flex;
		flex-direction: column;
	}

	&__body {
		display: flex;
		flex-direction: column;
		max-height: 55vh;
		overflow-y: scroll;
	}

	&__footer {
		display: flex;
		justify-content: end;
		margin-top: 0.5em;
	}

}
</style>
