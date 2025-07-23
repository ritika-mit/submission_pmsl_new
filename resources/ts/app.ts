import { createInertiaApp } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { createApp, h, ref, watch, type DefineComponent } from 'vue';
import '../scss/app.scss';

export function date(
	value: string,
	format: Intl.DateTimeFormatOptions = DateTime.DATE_MED,
) {
	return DateTime.fromISO(value).toLocaleString(format);
}

export function getValue<T>(key: keyof T, row: T, fallback = '-') {
	return (
		`${key.toString()}`.split('.').reduce((a: any, v) => a[v] || fallback, row) || fallback
	);
}

export function useReCaptcha(callback: (token: string) => void) {
	const element = ref<HTMLButtonElement>();

	const recaptchaCallback = () => setTimeout(() => {
		const { render } = window.grecaptcha;

		if (!element.value || typeof render !== 'function') {
			recaptchaCallback();
			return;
		}

		render(element.value, {
			sitekey: import.meta.env.VITE_RECAPTCHA_SITEKEY,
			callback
		})
	}, 100);

	watch(element, recaptchaCallback);

	return element;
}

export function basename(value: string) {
	return value?.substring(value?.lastIndexOf('/') + 1)
}

export function extension(value: string) {
	return value?.substring(value?.lastIndexOf('.') + 1)
}

export function wordCount(value: string) {
	return value.trim() ? (value.trim().match(/\s+/g)?.length || 0) + 1 : 0;
}

export const tinymceDefaultProps = {
	apiKey: import.meta.env.VITE_TINYMCE_APIKEY,
	init: {
		height: 500,
		menubar: false,
		plugins: [],
		toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
		visual: false
	}
}

const DOCUMENT_TITLE = document.title;

createInertiaApp({
	title: title => title ? `${title} | ${DOCUMENT_TITLE}` : DOCUMENT_TITLE,
	resolve: (name) => import.meta.glob<DefineComponent>('../vue/**/*.vue', { eager: true, })[`../vue/${name}.vue`],
	setup: ({ el, App, props, plugin }) => {
		createApp({ render: () => h(App, props) })
			.use(plugin)
			.mount(el);
	},
	progress: {
		color: '#B1D6FB',
		includeCSS: true,
		showSpinner: false,
	},
});
