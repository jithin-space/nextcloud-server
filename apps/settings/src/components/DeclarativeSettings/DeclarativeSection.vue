<template>
	<div class="declarative-settings-section">
		<NcSettingsSection
			v-for="formField in textFormFields"
			:key="formField.id"
			:name="t('settings', formField.title)"
			:description="sectionDescription(formField)">
			<NcSettingsInputText
				class="declarative-form-field-text"
				:label="formField.title"
				:value.sync="formFieldsData[formField.id].value"
				@update:value="onChangeDebounced(formField)"
				@submit="updateDeclarativeSettingsValue(formField)"/>
		</NcSettingsSection>

		<NcSettingsSection
			v-for="formField in passwordFormFields"
			:key="formField.id"
			:name="t('settings', formField.title)"
			:description="sectionDescription(formField)">
			<NcPasswordField
				class="declarative-form-field-password"
				:label="formField.title"
				:value.sync="formFieldsData[formField.id].value"
				@update:value="onChangeDebounced(formField)"/>
		</NcSettingsSection>

		<NcSettingsSection
			v-for="formField in selectFormFields"
			:key="formField.id"
			:name="t('settings', formField.title)"
			:description="sectionDescription(formField)">
			<NcSelect
				class="declarative-form-field-select"
				:label="formField.title"
				:options="formField.options"
				v-model="formField.value"
				@input="(value) => updateFormFieldDataValue(value, formField, true)"/>
		</NcSettingsSection>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showSuccess, showError } from '@nextcloud/dialogs'
import debounce from 'debounce'
import NcSettingsSection from '@nextcloud/vue/dist/Components/NcSettingsSection.js'
import NcSettingsInputText from '@nextcloud/vue/dist/Components/NcSettingsInputText.js'
import NcPasswordField from '@nextcloud/vue/dist/Components/NcPasswordField.js'
import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js'
import NcDateTimePicker from '@nextcloud/vue/dist/Components/NcDateTimePicker.js'

export default {
	name: 'DeclarativeSection',
	components: {
		NcSettingsSection,
		NcSettingsInputText,
		NcPasswordField,
		NcSelect,
		NcDateTimePicker,
	},
	props: {
		form: {
			type: Object,
			required: true,
			default: () => t('settings', 'Declarative settings form not found'),
		},
	},
	data() {
		return {
			formFieldsData: {},
		}
	},
	beforeMount() {
		this.initFormFieldsData()
	},
	computed: {
		formApp() {
			return this.form.app || ''
		},
		textFormFields() {
			return this.form.fields.filter(formField => formField.type === 'string')
		},
		passwordFormFields() {
			return this.form.fields.filter(formField => formField.type === 'password')
		},
		selectFormFields() {
			return this.form.fields.filter(formField => formField.type === 'select')
		},
	},
	methods: {
		initFormFieldsData() {
			this.form.fields.forEach((formField) => {
				this.formFieldsData[formField.id] = {
					value: formField.value
				}
			})
		},

		updateFormFieldDataValue(value, formField, update = false) {
			this.formFieldsData[formField.id].value = value
			if (update) {
				this.updateDeclarativeSettingsValue(formField)
			}
		},

		updateDeclarativeSettingsValue(formField) {
			axios.post(generateUrl('settings/api/declarative'), {
				app: this.formApp,
				fieldId: formField.id,
				value: this.formFieldsData[formField.id].value,
			}).then(res => {
				if (res.status === 200) {
					showSuccess(t('settings', 'Declarative setting updated'))
				}
			}).catch(err => {
				console.debug(err)
				showError(t('settings', 'Failed to update declarative setting'))
			})
		},

		onChangeDebounced: debounce(function(formField) {
			this.updateDeclarativeSettingsValue(formField)
		}, 1000),

		sectionDescription(formField) {
			return Object.hasOwn(formField, 'description') ? t('settings', formField.description) : t('settings', '{appid} app declarative settings section', { appid: this.formApp })
		},
	},
}
</script>
