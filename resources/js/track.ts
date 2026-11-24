import type { router } from '@inertiajs/react';

export function trackSiteAnalytics(
	inertia: typeof router,
	initialPage?: string | null,
): void {
	let page = initialPage;
	let last = '';

	function send(event: string): void {
		if (!page) return;
		void fetch('/site-analytics/event', {
			method: 'POST',
			headers: {
				Accept: 'application/json',
				'Content-Type': 'application/json',
			},
			body: JSON.stringify({ page, event }),
			credentials: 'omit',
			referrerPolicy: 'no-referrer',
			keepalive: true,
		}).catch(() => {
			// Сбой фоновой статистики не мешает работе страницы.
		});
	}

	function view(): void {
		const address = window.location.pathname;
		const key = `${page}:${address}`;
		if (key === last) return;
		last = key;
		send('page_view');
	}

	inertia.on('navigate', (event) => {
		page = event.detail.page.props.analyticsPage as string | null | undefined;
		view();
	});
	document.addEventListener(
		'click',
		(event) => {
			if (!(event.target instanceof Element)) return;
			const action = event.target.closest<HTMLElement>('[data-site-analytics]')
				?.dataset.siteAnalytics;
			if (action) send(action);
		},
		{ capture: true },
	);
	view();
}
