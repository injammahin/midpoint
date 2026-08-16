.cp-page {
    display: grid;
    gap: 18px;
}


.cp-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}


.cp-header h2 {
    margin: 0;
    color: var(--admin-heading);
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 24px;
}


.cp-header p {
    margin: 6px 0 0;
    max-width: 680px;
    color: var(--admin-muted);
    font-size: 13px;
    line-height: 1.6;
}


.cp-public-link {
    display: inline-flex;
    min-height: 40px;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 0 14px;
    border: 1px solid var(--admin-border);
    border-radius: 10px;
    color: var(--admin-heading);
    background: var(--admin-surface);
    box-shadow: var(--admin-shadow);
    text-decoration: none;
    font-size: 11px;
    font-weight: 800;
}


.cp-public-link:hover {
    border-color: var(--admin-accent);
    color: var(--admin-accent);
}


.cp-alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 13px 14px;
    border: 1px solid var(--admin-border);
    border-radius: 11px;
    color: var(--admin-text);
    background: var(--admin-surface);
    font-size: 12px;
}


.cp-alert.success {
    border-color:
        color-mix(
            in srgb,
            var(--admin-accent) 38%,
            var(--admin-border)
        );

    background: var(--admin-accent-soft);
}


.cp-alert.error {
    border-color:
        color-mix(
            in srgb,
            var(--admin-danger) 38%,
            var(--admin-border)
        );
}


.cp-alert ul {
    margin: 7px 0 0 18px;
    padding: 0;
}


.cp-tabs {
    display: flex;
    gap: 7px;
    overflow-x: auto;
    padding: 7px;
    border: 1px solid var(--admin-border);
    border-radius: 13px;
    background: var(--admin-surface);
    box-shadow: var(--admin-shadow);
}


.cp-tab {
    flex: 0 0 auto;
    min-height: 40px;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 0 13px;
    border: 1px solid transparent;
    border-radius: 9px;
    color: var(--admin-muted);
    background: transparent;
    cursor: pointer;
    font: inherit;
    font-size: 11px;
    font-weight: 800;
}


.cp-tab:hover {
    color: var(--admin-heading);
    background: var(--admin-surface-hover);
}


.cp-tab.active {
    border-color:
        color-mix(
            in srgb,
            var(--admin-accent) 30%,
            var(--admin-border)
        );

    color: var(--admin-accent);
    background: var(--admin-accent-soft);
}


.cp-panel {
    display: none;
}


.cp-panel.active {
    display: block;
}


.cp-card {
    overflow: hidden;
    border: 1px solid var(--admin-border);
    border-radius: 14px;
    background: var(--admin-surface);
    box-shadow: var(--admin-shadow);
}


.cp-card + .cp-card {
    margin-top: 16px;
}


.cp-card-head {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 15px 16px;
    border-bottom: 1px solid var(--admin-border-soft);
}


.cp-card-icon {
    width: 36px;
    height: 36px;
    flex: 0 0 36px;
    display: grid;
    place-items: center;
    border-radius: 10px;
    color: var(--admin-accent);
    background: var(--admin-accent-soft);
}


.cp-card-head h3 {
    margin: 0;
    color: var(--admin-heading);
    font-size: 14px;
}


.cp-card-head p {
    margin: 3px 0 0;
    color: var(--admin-muted);
    font-size: 10px;
}


.cp-card-body {
    padding: 16px;
}


.cp-grid {
    display: grid;
    gap: 14px;
}


.cp-grid.two {
    grid-template-columns:
        repeat(
            2,
            minmax(0, 1fr)
        );
}


.cp-field {
    display: grid;
    gap: 7px;
}


.cp-field label {
    color: var(--admin-heading);
    font-size: 11px;
    font-weight: 800;
}


.cp-field small {
    color: var(--admin-muted);
    font-size: 10px;
    line-height: 1.5;
}


.cp-field input,
.cp-field textarea,
.cp-field select {
    width: 100%;
    border: 1px solid var(--admin-border);
    border-radius: 9px;
    outline: none;
    color: var(--admin-text);
    background: var(--admin-surface-soft);
    font: inherit;
    font-size: 12px;
}


.cp-field input,
.cp-field select {
    min-height: 41px;
    padding: 0 11px;
}


.cp-field textarea {
    min-height: 110px;
    resize: vertical;
    padding: 10px 11px;
    line-height: 1.55;
}


.cp-field input:focus,
.cp-field textarea:focus,
.cp-field select:focus {
    border-color: var(--admin-accent);

    box-shadow:
        0 0 0 3px
        color-mix(
            in srgb,
            var(--admin-accent) 14%,
            transparent
        );
}


.cp-repeater {
    display: grid;
    gap: 10px;
}


.cp-repeat-item {
    position: relative;
    display: grid;
    gap: 12px;
    padding: 14px;
    border: 1px solid var(--admin-border-soft);
    border-radius: 11px;
    background: var(--admin-surface-soft);
}


.cp-repeat-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}


.cp-repeat-top strong {
    color: var(--admin-heading);
    font-size: 11px;
}


.cp-remove {
    width: 31px;
    height: 31px;
    display: grid;
    place-items: center;
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    color: var(--admin-danger);
    background: var(--admin-surface);
    cursor: pointer;
}


.cp-add {
    margin-top: 11px;
    min-height: 37px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 0 12px;
    border: 1px dashed var(--admin-accent);
    border-radius: 9px;
    color: var(--admin-accent);
    background: var(--admin-accent-soft);
    cursor: pointer;
    font: inherit;
    font-size: 10px;
    font-weight: 800;
}


.cp-actions {
    position: sticky;
    bottom: 12px;
    z-index: 20;
    display: flex;
    justify-content: flex-end;
    margin-top: 18px;
    padding: 11px;
    border: 1px solid var(--admin-border);
    border-radius: 12px;
    background:
        color-mix(
            in srgb,
            var(--admin-surface) 93%,
            transparent
        );
    backdrop-filter: blur(12px);
}


.cp-save {
    min-height: 41px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 0 17px;
    border: 0;
    border-radius: 9px;
    color: #FFFFFF;
    background: var(--admin-accent-strong);
    cursor: pointer;
    font: inherit;
    font-size: 11px;
    font-weight: 800;
}


@media(max-width: 760px) {

    .cp-header {
        flex-direction: column;
    }


    .cp-public-link {
        width: 100%;
    }


    .cp-grid.two {
        grid-template-columns: 1fr;
    }


    .cp-actions {
        position: static;
    }


    .cp-save {
        width: 100%;
    }

}