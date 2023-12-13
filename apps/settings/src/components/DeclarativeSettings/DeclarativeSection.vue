<template>
	<NcSettingsSection
		class="declarative-settings-section"
		:name="t(form.app, form.title)"
		:description="t(form.app, form.description)">
		<div v-for="formField in textFormFields"
			 :key="formField.id"
			 class="declarative-form-field">
			<label :for="formField.id + '_field'">{{ t(form.app, formField.title) }}</label>
			<NcInputField
				:type="formField.type"
				:label-outside="true"
				:value.sync="formFieldsData[formField.id].value"
				@update:value="onChangeDebounced(formField)"
				@submit="updateDeclarativeSettingsValue(formField)"/>
			<span class="hint">{{ formField.description }}</span>
		</div>

		<div v-for="formField in selectFormFields"
			 :key="formField.id"
			 class="declarative-form-field">
			<label :for="formField.id + '_field'">{{ t(form.app, formField.title) }}</label>
			<NcSelect
				:id="formField.id + '_field'"
				:options="formField.options"
				v-model="formField.value"
				@input="(value) => updateFormFieldDataValue(value, formField, true)"/>
			<span class="hint">{{ formField.description }}</span>
		</div>

		<div v-for="formField in checkboxFormFields"
			 :key="formField.id"
			 class="declarative-form-field">
			<div class="label-outside">
				<label :for="formField.id + '_field'">{{ t(form.app, formField.title) }}</label>
				<NcCheckboxRadioSwitch
					:id="formField.id + '_field'"
					:checked="Boolean(formField.value)"
					@update:checked="(value) => {
						formField.value = value
						updateFormFieldDataValue(+value, formField, true)
					}">
					{{ t(formField.app, formField.label) }}
				</NcCheckboxRadioSwitch>
				<span class="hint">{{ formField.description }}</span>
			</div>
		</div>

		<div v-for="formField in multiCheckboxFormFields"
			 :key="formField.id"
			 class="declarative-form-field declarative-form-field-multi_checkbox">
			<label :for="formField.id + '_field'">{{ t(form.app, formField.title) }}</label>
			<NcCheckboxRadioSwitch
				v-for="option in formField.options"
				:id="formField.id + '_field_' + option.value"
				:key="option.value"
				:checked.sync="formFieldsData[formField.id].value[option.value]"
				@update:checked="(value) => {
					formFieldsData[formField.id].value[option.value] = value
					// Update without re-generating initial formFieldsData.value object as the link to components are lost
					updateDeclarativeSettingsValue(formField, JSON.stringify(formFieldsData[formField.id].value))
				}">
				{{ t(formField.app, option.name) }}
			</NcCheckboxRadioSwitch>
			<span class="hint">{{ formField.description }}</span>
		</div>

		<div v-for="formField in radioFormFields"
			 :key="formField.id"
			 class="declarative-form-field declarative-form-field-radio">
			<label :for="formField.id + '_field'">{{ t(form.app, formField.title) }}</label>
			<NcCheckboxRadioSwitch
				v-for="option in formField.options"
				:key="option.id"
				:value="option.value"
				type="radio"
				:checked.sync="formField.value"
				@update:checked="(value) => updateFormFieldDataValue(value, formField, true)">
				{{ t(formField.app, option.name) }}
			</NcCheckboxRadioSwitch>
			<span class="hint">{{ formField.description }}</span>
		</div>
	</NcSettingsSection>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showSuccess, showError } from '@nextcloud/dialogs'
import debounce from 'debounce'
import NcSettingsSection from '@nextcloud/vue/dist/Components/NcSettingsSection.js'
import NcInputField from '@nextcloud/vue/dist/Components/NcInputField.js'
import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'

export default {
	name: 'DeclarativeSection',
	components: {
		NcSettingsSection,
		NcInputField,
		NcSelect,
		NcCheckboxRadioSwitch,
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
			return this.form.fields.filter(formField => ['text', 'password', 'email', 'tel', 'url', 'search', 'number'].includes(formField.type))
		},
		selectFormFields() {
			return this.form.fields.filter(formField => formField.type === 'select')
		},
		radioFormFields() {
			return this.form.fields.filter(formField => formField.type === 'radio')
		},
		checkboxFormFields() {
			return this.form.fields.filter(formField => formField.type === 'checkbox')
		},
		multiCheckboxFormFields() {
			return this.form.fields.filter(formField => formField.type === 'multi-checkbox')
		},
	},
	methods: {
		initFormFieldsData() {
			this.form.fields.forEach((formField) => {
				if (formField.type === 'bool') {
					formField.value = +formField.value
				}
				if (formField.type === 'multi-checkbox') {
					if (formField.value === '') {
						// Init formFieldsData with splitted options
						formField.value = {}
						formField.options.forEach(option => {
							formField.value[option.value] = false
						})
					} else {
						formField.value = JSON.parse(formField.value)
						// Merge possible new options
						formField.options.forEach(option => {
							if (!formField.value.hasOwnProperty(option.value)) {
								formField.value[option.value] = false
							}
						})
					}
				}
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

		updateDeclarativeSettingsValue(formField, value = null) {
			return axios.post(generateUrl('settings/api/declarative'), {
				app: this.formApp,
				fieldId: formField.id,
				value: value === null ? this.formFieldsData[formField.id].value : value,
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
	},
}
</script>

<style lang="scss" scoped>
.declarative-form-field {
	margin: 20px 0;
	padding: 10px 0;
	border-bottom: 1px solid var(--color-border-dark);

	&:last-child {
		border-bottom: none;
	}

	.label-outside {
		display: flex;
		align-items: center;

		& > label {
			padding-top: 7px;
			padding-right: 14px;
			white-space: nowrap;
		}
	}

	.hint {
		color: var(--color-text-maxcontrast);
		margin-left: 8px;
		padding-top: 5px;
	}

	&-radio, &-multi_checkbox {
		max-height: 300px;
		overflow-y: auto;
	}
}
</style>
