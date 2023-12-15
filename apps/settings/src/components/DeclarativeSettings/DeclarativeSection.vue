<template>
	<NcSettingsSection
		class="declarative-settings-section"
		:name="t(form.app, form.title)"
		:description="t(form.app, form.description)"
		:doc-url="form.doc_url || ''">
		<div v-for="formField in formFields"
			 :key="formField.id"
			 class="declarative-form-field"
			:class="{
				'declarative-form-field-text': isTextFormFields(formField),
				'declarative-form-field-select': formField.type === 'select',
				'declarative-form-field-checkbox': formField.type === 'checkbox',
				'declarative-form-field-multi_checkbox': formField.type === 'multi-checkbox',
				'declarative-form-field-radio': formField.type === 'radio',
			}">

			<template v-if="isTextFormFields(formField)">
				<label :for="formField.id + '_field'">{{ t(form.app, formField.title) }}</label>
				<NcInputField
					:type="formField.type"
					:label-outside="true"
					:value.sync="formFieldsData[formField.id].value"
					:placeholder="t(form.app, formField.placeholder)"
					@update:value="onChangeDebounced(formField)"
					@submit="updateDeclarativeSettingsValue(formField)"/>
				<span class="hint">{{ formField.description }}</span>
			</template>

			<template v-if="formField.type === 'select'">
				<label :for="formField.id + '_field'">{{ t(form.app, formField.title) }}</label>
				<NcSelect
					:id="formField.id + '_field'"
					:options="formField.options"
					:placeholder="t(form.app, formField.placeholder)"
					v-model="formField.value"
					@input="(value) => updateFormFieldDataValue(value, formField, true)"/>
				<span class="hint">{{ formField.description }}</span>
			</template>

			<template v-if="formField.type === 'multi-select'">
				<label :for="formField.id + '_field'">{{ t(form.app, formField.title) }}</label>
				<NcSelect
					:id="formField.id + '_field'"
					:options="formField.options"
					:placeholder="t(form.app, formField.placeholder)"
					:multiple="true"
					v-model="formField.value"
					@input="(value) => {
						formFieldsData[formField.id].value = value
						updateDeclarativeSettingsValue(formField, JSON.stringify(formFieldsData[formField.id].value))
					}
				"/>
				<span class="hint">{{ formField.description }}</span>
			</template>

			<template v-if="formField.type === 'checkbox'">
				<div class="label-outside">
					<label :for="formField.id + '_field'">{{ t(form.app, formField.title) }}</label>
					<NcCheckboxRadioSwitch
						:id="formField.id + '_field'"
						:checked="Boolean(formField.value)"
						@update:checked="(value) => {
							formField.value = value
							updateFormFieldDataValue(+value, formField, true)
						}
					">
						{{ t(formField.app, formField.label) }}
					</NcCheckboxRadioSwitch>
					<span class="hint">{{ t(form.app, formField.description) }}</span>
				</div>
			</template>

			<template v-if="formField.type === 'multi-checkbox'">
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
					}
				">
					{{ t(formField.app, option.name) }}
				</NcCheckboxRadioSwitch>
				<span class="hint">{{ formField.description }}</span>
			</template>

			<template v-if="formField.type === 'radio'">
				<label :for="formField.id + '_field'">{{ t(form.app, formField.title) }}</label>
				<NcCheckboxRadioSwitch
					v-for="option in formField.options"
					:key="option.value"
					:value="option.value"
					type="radio"
					:checked.sync="formField.value"
					@update:checked="(value) => updateFormFieldDataValue(value, formField, true)">
					{{ t(formField.app, option.name) }}
				</NcCheckboxRadioSwitch>
				<span class="hint">{{ formField.description }}</span>
			</template>
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
		formFields() {
			return this.form.fields || []
		},
		isTextFormFields() {
			return (formField) => ['text', 'password', 'email', 'tel', 'url', 'search', 'number'].includes(formField.type)
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
						// Init formFieldsData from options
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
						// Remove options that are not in the form anymore
						Object.keys(formField.value).forEach(key => {
							if (!formField.options.find(option => option.value === key)) {
								delete formField.value[key]
							}
						})
					}
				}
				if (formField.type === 'multi-select') {
					if (formField.value === '') {
						// Init empty array for multi-select
						formField.value = []
					} else {
						// JSON decode an array of multiple values set
						formField.value = JSON.parse(formField.value)
					}
				}
				this.formFieldsData[formField.id] = {
					value: formField.value
				}
				// TODO: Fix the case when the formField.value object is not bound to the components to be updated on change
				console.debug('formField.value: ', formField.value)
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
		max-height: 250px;
		overflow-y: auto;
	}
}
</style>
