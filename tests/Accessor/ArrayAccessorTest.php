<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Accessor;

use PHPUnit\Framework\TestCase;

class ArrayAccessorTest extends TestCase {
    // region string
    public function testStringOfNoKey(): void {
        self::assertSame(
            '',
            ArrayAccessor::of([])->string('foo'),
        );
    }

    public function testStringUseDefault(): void {
        self::assertSame(
            'default',
            ArrayAccessor::of([])->string('foo', 'default'),
        );
    }

    public function testStringOfNull(): void {
        self::assertSame(
            '',
            ArrayAccessor::of(['foo' => null])->string('foo'),
        );
    }

    public function testStringOfInteger(): void {
        self::assertSame(
            '1',
            ArrayAccessor::of(['foo' => 1])->string('foo'),
        );
    }

    public function testStringOfBoolean(): void {
        self::assertSame(
            '1',
            ArrayAccessor::of(['foo' => true])->string('foo'),
        );
    }

    public function testStringBailsOnArray(): void {
        self::expectException(ArrayAccessorException::class);
        ArrayAccessor::of(['foo' => ['a', 'b']])->string('foo');
    }
    // endregion

    // region maybeString
    public function testMaybeStringOfNoKey(): void {
        self::assertNull(
            ArrayAccessor::of([])->maybeString('foo'),
        );
    }

    public function testMaybeStringOfNull(): void {
        self::assertNull(
            ArrayAccessor::of(['foo' => null])->maybeString('foo'),
        );
    }

    public function testMaybeStringOfInteger(): void {
        self::assertSame(
            '1',
            ArrayAccessor::of(['foo' => 1])->maybeString('foo'),
        );
    }

    public function testMaybeStringOfBoolean(): void {
        self::assertSame(
            '1',
            ArrayAccessor::of(['foo' => true])->maybeString('foo'),
        );
    }

    public function testMaybeStringBailsOnArray(): void {
        self::expectException(ArrayAccessorException::class);
        ArrayAccessor::of(['foo' => ['a', 'b']])->maybeString('foo');
    }
    // endregion

    // region requireString
    public function testRequireStringBailsNoKey(): void {
        self::expectException(ArrayAccessorException::class);
        ArrayAccessor::of([])->requireString('foo');
    }

    public function testRequireStringBailsOnNull(): void {
        self::expectException(ArrayAccessorException::class);
        ArrayAccessor::of(['foo' => null])->requireString('foo');
    }

    public function testRequireStringOfInteger(): void {
        self::assertSame(
            '1',
            ArrayAccessor::of(['foo' => 1])->requireString('foo'),
        );
    }

    public function testRequireStringOfBoolean(): void {
        self::assertSame(
            '1',
            ArrayAccessor::of(['foo' => true])->requireString('foo'),
        );
    }

    public function testRequireStringBailsOnArray(): void {
        self::expectException(ArrayAccessorException::class);
        ArrayAccessor::of(['foo' => ['a', 'b']])->requireString('foo');
    }
    // endregion

    // region int
    public function testIntCastsString(): void {
        self::assertSame(
            1,
            ArrayAccessor::of(['foo' => "1"])->int("foo"),
        );
    }

    public function testIntCastsBoolean(): void {
        self::assertSame(
            1,
            ArrayAccessor::of(['foo' => true])->int("foo"),
        );
    }

    public function testIntBailsOnInvalidString(): void {
        self::expectException(ArrayAccessorException::class);
        ArrayAccessor::of(['foo' => "bar"])->int("foo");
    }
    // endregion

    // region bool
    public function testBoolCastsStringTrue(): void {
        self::assertTrue(
            ArrayAccessor::of(['foo' => "1"])->bool("foo"),
        );
    }

    public function testBoolCastsStringFalse(): void {
        self::assertFalse(
            ArrayAccessor::of(['foo' => "0"])->bool("foo"),
        );
    }

    public function testBoolCastsIntTrue(): void {
        self::assertTrue(
            ArrayAccessor::of(['foo' => 1])->bool("foo"),
        );
    }

    public function testBoolCastsIntFalse(): void {
        self::assertFalse(
            ArrayAccessor::of(['foo' => 0])->bool("foo"),
        );
    }

    public function testBoolBailsOnOtherNumbers(): void {
        self::expectException(ArrayAccessorException::class);
        ArrayAccessor::of(['foo' => 10])->bool("foo");
    }

    public function testBoolBailsOnInvalidString(): void {
        self::expectException(ArrayAccessorException::class);
        ArrayAccessor::of(['foo' => "bar"])->bool("foo");
    }
    // endregion

    // region at
    public function testAtNoKey(): void {
        self::assertEquals(
            [],
            ArrayAccessor::of([])->at('weekdays')->allStrings(),
        );
    }

    public function testAtEmptyArray(): void {
        self::assertEquals(
            [],
            ArrayAccessor::of(['weekdays' => []])->at('weekdays')->allStrings(),
        );
    }

    public function testAtFilledArray(): void {
        self::assertEquals(
            ['monday'],
            ArrayAccessor::of(['weekdays' => ['monday']])->at('weekdays')->allStrings(),
        );
    }

    public function testMaybeAt(): void {
        self::assertNull(
            ArrayAccessor::of([])->maybeAt('weekdays'),
        );
    }

    public function testMaybeAtBailsOnStringKey(): void {
        self::expectException(ArrayAccessorException::class);
        ArrayAccessor::of(['weekdays' => 'string'])->maybeAt('weekdays');
    }

    public function testMaybeAtHavingArrayKey(): void {
        self::assertSame(
            'foo',
            ArrayAccessor::of(['weekdays' => ['key' => 'foo']])->maybeAt('weekdays')->string('key'),
        );
    }
    // endregion
}
