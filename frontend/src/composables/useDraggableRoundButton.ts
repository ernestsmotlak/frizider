import { onUnmounted, ref, type Ref } from "vue";

const ROUND_BTN_SIZE_PX = 40;

export interface UseDraggableRoundButtonOptions {
    initialLeft?: number;
    initialTop?: number;
}

export interface UseDraggableRoundButtonReturn {
    containerRef: Ref<HTMLElement | null>;
    roundButtonLeft: Ref<number>;
    roundButtonTop: Ref<number>;
    roundBtnDragging: Ref<boolean>;
    onRoundBtnPointerDown: (e: MouseEvent | TouchEvent) => void;
}

export function useDraggableRoundButton(
    options: UseDraggableRoundButtonOptions = {},
): UseDraggableRoundButtonReturn {
    const initialLeft = options.initialLeft ?? 18;
    const initialTop = options.initialTop ?? 52.5;

    const containerRef = ref<HTMLElement | null>(null);
    const roundButtonLeft = ref(initialLeft);
    const roundButtonTop = ref(initialTop);
    const roundBtnDragging = ref(false);
    const roundBtnOffset = ref({ x: 0, y: 0 });

    function getCardRect(): DOMRect | null {
        return containerRef.value?.getBoundingClientRect() ?? null;
    }

    function clampRoundBtnPosition(
        left: number,
        top: number,
    ): { left: number; top: number } {
        const rect = getCardRect();
        if (!rect) return { left: roundButtonLeft.value, top: roundButtonTop.value };
        const maxLeft = Math.max(0, rect.width - ROUND_BTN_SIZE_PX);
        const maxTop = Math.max(0, rect.height - ROUND_BTN_SIZE_PX);
        return {
            left: Math.max(0, Math.min(maxLeft, left)),
            top: Math.max(0, Math.min(maxTop, top)),
        };
    }

    function applyRoundBtnPosition(clientX: number, clientY: number): void {
        const rect = getCardRect();
        if (!rect) return;
        const left = clientX - rect.left - roundBtnOffset.value.x;
        const top = clientY - rect.top - roundBtnOffset.value.y;
        const clamped = clampRoundBtnPosition(left, top);
        roundButtonLeft.value = clamped.left;
        roundButtonTop.value = clamped.top;
    }

    function onDocumentPointerMove(e: MouseEvent): void {
        if (!roundBtnDragging.value) return;
        applyRoundBtnPosition(e.clientX, e.clientY);
    }

    function onDocumentTouchMove(e: TouchEvent): void {
        if (!roundBtnDragging.value || e.touches.length === 0) return;
        e.preventDefault();
        applyRoundBtnPosition(e.touches[0].clientX, e.touches[0].clientY);
    }

    function onDocumentPointerUp(): void {
        roundBtnDragging.value = false;
        document.removeEventListener("mousemove", onDocumentPointerMove);
        document.removeEventListener("mouseup", onDocumentPointerUp);
        document.removeEventListener("touchmove", onDocumentTouchMove);
        document.removeEventListener("touchend", onDocumentPointerUp);
    }

    function onRoundBtnPointerDown(e: MouseEvent | TouchEvent): void {
        const clientX = "touches" in e ? e.touches[0].clientX : e.clientX;
        const clientY = "touches" in e ? e.touches[0].clientY : e.clientY;
        if ("touches" in e) e.preventDefault();
        const rect = getCardRect();
        if (!rect) return;
        roundBtnOffset.value = {
            x: clientX - (rect.left + roundButtonLeft.value),
            y: clientY - (rect.top + roundButtonTop.value),
        };
        roundBtnDragging.value = true;
        document.addEventListener("mousemove", onDocumentPointerMove);
        document.addEventListener("mouseup", onDocumentPointerUp);
        document.addEventListener("touchmove", onDocumentTouchMove, { passive: false });
        document.addEventListener("touchend", onDocumentPointerUp);
    }

    onUnmounted(() => {
        document.removeEventListener("mousemove", onDocumentPointerMove);
        document.removeEventListener("mouseup", onDocumentPointerUp);
        document.removeEventListener("touchmove", onDocumentTouchMove);
        document.removeEventListener("touchend", onDocumentPointerUp);
    });

    return {
        containerRef,
        roundButtonLeft,
        roundButtonTop,
        roundBtnDragging,
        onRoundBtnPointerDown,
    };
}
