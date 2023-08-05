<?php

namespace Sphinx\Tests;

use Sphinx\SphinxClient;

/**
 * SphinxClient test cases.
 * Some test cases are ported from Perl Sphinx::Search, (c) Jon Schutz.
 *
 * @author Chris Heng <hengkuanyen@gmail.com>
 */
class SphinxClientTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorDefaults()
    {
        $sphinx = new SphinxClient();
        $this->assertSame($sphinx->host, 'localhost');
        $this->assertSame($sphinx->port, 9312);
        $this->assertSame($sphinx->path, false);
        $this->assertSame($sphinx->socket, false);
        $this->assertSame($sphinx->offset, 0);
        $this->assertSame($sphinx->limit, 20);
        $this->assertSame($sphinx->mode, SphinxClient::SPH_MATCH_ALL);
        $this->assertSame($sphinx->weights, array());
        $this->assertSame($sphinx->sort, SphinxClient::SPH_SORT_RELEVANCE);
        $this->assertSame($sphinx->sortby, '');
        $this->assertSame($sphinx->minid, 0);
        $this->assertSame($sphinx->maxid, 0);
        $this->assertSame($sphinx->filters, array());
        $this->assertSame($sphinx->groupby, '');
        $this->assertSame($sphinx->groupfunc, SphinxClient::SPH_GROUPBY_DAY);
        $this->assertSame($sphinx->groupsort, '@group desc');
        $this->assertSame($sphinx->groupdistinct, '');
        $this->assertSame($sphinx->maxmatches, 1000);
        $this->assertSame($sphinx->cutoff, 0);
        $this->assertSame($sphinx->retrycount, 0);
        $this->assertSame($sphinx->retrydelay, 0);
        $this->assertSame($sphinx->anchor, array());
        $this->assertSame($sphinx->indexweights, array());
        $this->assertSame($sphinx->ranker, SphinxClient::SPH_RANK_PROXIMITY_BM25);
        $this->assertSame($sphinx->rankexpr, '');
        $this->assertSame($sphinx->maxquerytime, 0);
        $this->assertSame($sphinx->fieldweights, array());
        $this->assertSame($sphinx->overrides, array());
        $this->assertSame($sphinx->select, '*');
        $this->assertSame($sphinx->error, '');
        $this->assertSame($sphinx->warning, '');
        $this->assertSame($sphinx->connerror, false);
        $this->assertSame($sphinx->reqs, array());
        $this->assertSame($sphinx->mbenc, '');
        $this->assertSame($sphinx->arrayresult, false);
        $this->assertSame($sphinx->timeout, 0);
    }

    /**
     * @dataProvider provideBigintUnsigned
     */
    public function testPackBigintUnsigned($bigint)
    {
        $sphinx = new SphinxClient();
        // packing and unpacking may not preserve data type
        $this->assertEquals($sphinx->unpackU64($sphinx->packU64($bigint)), $bigint);
    }

    public function provideBigintUnsigned()
    {
        return array(
            array(0),
            array(1),
            array(0x7FFFFFFF),
            array(0x80000000),
            array(0xFFFFFFFF),
            array('4294967296'),
            array('9223372036854775807'),
            array('9223372036854775808'),
            array('18446744073709551615')
        );
    }

    /**
     * @dataProvider provideBigintSigned
     */
    public function testPackBigintSigned($bigint)
    {
        $sphinx = new SphinxClient();
        // packing and unpacking may not preserve data type
        $this->assertEquals($sphinx->unpackI64($sphinx->packI64($bigint)), $bigint);
    }

    public function provideBigintSigned()
    {
        return array(
            array(0),
            array(1),
            array(-1),
            array(0x7FFFFFFF),
            array(0x80000000),
            array(0xFFFFFFFF),
            array(-0x7FFFFFFF),
            array(-0xFFFFFFFF),
            array('-4294967296'),
            array('-9223372036854775807')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetServerWithInvalidHostShouldThrowException()
    {
        $sphinx = new SphinxClient();
        $sphinx->setServer(123);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetServerWithNegativePortShouldThrowException()
    {
        $sphinx = new SphinxClient();
        $sphinx->setServer('localhost', -1);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetServerWithInvalidPortShouldThrowException()
    {
        $sphinx = new SphinxClient();
        $sphinx->setServer('localhost', 65536);
    }

    public function testSetServerWithSocketShouldSetPath()
    {
        $sphinx = new SphinxClient();
        $sphinx->setServer('/tmp/searchd.sock');
        $this->assertSame($sphinx->path, 'unix:///tmp/searchd.sock');

        $sphinx = new SphinxClient();
        $sphinx->setServer('unix:///tmp/searchd.sock');
        $this->assertSame($sphinx->path, 'unix:///tmp/searchd.sock');
    }

    public function testSetServerWithAddressShouldSetHostAndPort()
    {
        $sphinx = new SphinxClient();
        $sphinx->setServer('localhost', 3312);
        $this->assertSame($sphinx->host, 'localhost');
        $this->assertSame($sphinx->port, 3312);
    }

    public function testSetServerWithoutPortShouldSetDefaultPort()
    {
        $sphinx = new SphinxClient();
        $sphinx->setServer('localhost');
        $this->assertSame($sphinx->port, 9312);
    }

    public function testSetConnectTimeout()
    {
        $sphinx = new SphinxClient();
        $sphinx->setConnectTimeout(10);
        $this->assertSame($sphinx->timeout, 10);
    }

    public function testSetLimits()
    {
        $sphinx = new SphinxClient();
        $sphinx->setLimits(100, 50, 2000, 5000);
        $this->assertSame($sphinx->offset, 100);
        $this->assertSame($sphinx->limit, 50);
        $this->assertSame($sphinx->maxmatches, 2000);
        $this->assertSame($sphinx->cutoff, 5000);
    }

    public function testSetMaxQueryTime()
    {
        $sphinx = new SphinxClient();
        $sphinx->setMaxQueryTime(10);
        $this->assertSame($sphinx->maxquerytime, 10);
    }

    public function testSetMatchMode()
    {
        $sphinx = new SphinxClient();
        $sphinx->setMatchMode(SphinxClient::SPH_MATCH_EXTENDED2);
        $this->assertSame($sphinx->mode, SphinxClient::SPH_MATCH_EXTENDED2);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetMatchModeWithInvalidModeShouldThrowException()
    {
        $sphinx = new SphinxClient();
        $sphinx->setMatchMode(7);
    }

    public function testSetRankingMode()
    {
        $sphinx = new SphinxClient();
        $sphinx->setRankingMode(SphinxClient::SPH_RANK_EXPR, '@count asc');
        $this->assertSame($sphinx->ranker, SphinxClient::SPH_RANK_EXPR);
        $this->assertSame($sphinx->rankexpr, '@count asc');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetRankingModeWithInvalidRankerShouldThrowException()
    {
        $sphinx = new SphinxClient();
        $sphinx->setRankingMode(10);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetRankingModeWithExpressionRankerWithoutExpressionShouldThrowException()
    {
        $sphinx = new SphinxClient();
        $sphinx->setRankingMode(SphinxClient::SPH_RANK_EXPR);
    }

    public function testSetSortMode()
    {
        $sphinx = new SphinxClient();
        $sphinx->setSortMode(SphinxClient::SPH_SORT_RELEVANCE);
        $this->assertSame($sphinx->sort, SphinxClient::SPH_SORT_RELEVANCE);

        $sphinx = new SphinxClient();
        $sphinx->setSortMode(SphinxClient::SPH_SORT_EXPR, '@count asc');
        $this->assertSame($sphinx->sort, SphinxClient::SPH_SORT_EXPR);
        $this->assertSame($sphinx->sortby, '@count asc');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetSortModeWithInvalidSortModeShouldThrowException()
    {
        $sphinx = new SphinxClient();
        $sphinx->setSortMode(6);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetSortModeWithNonRelevanceSortWithoutExpressionShouldThrowException()
    {
        $sphinx = new SphinxClient();
        $sphinx->setSortMode(SphinxClient::SPH_SORT_EXPR);
    }

    public function testSetFieldWeights()
    {
        $sphinx = new SphinxClient();
        $sphinx->setFieldWeights(array('field1' => 10, 'field2' => 200));
        $this->assertSame($sphinx->fieldweights, array('field1' => 10, 'field2' => 200));
    }

    public function testSetIndexWeights()
    {
        $sphinx = new SphinxClient();
        $sphinx->setIndexWeights(array('index1' => 20, 'index2' => 5));
        $this->assertSame($sphinx->indexweights, array('index1' => 20, 'index2' => 5));
    }

    public function testSetIdRange()
    {
        $sphinx = new SphinxClient();
        $sphinx->setIdRange(1337, 9001);
        $this->assertSame($sphinx->minid, 1337);
        $this->assertSame($sphinx->maxid, 9001);

        $sphinx = new SphinxClient();
        $sphinx->setIdRange('184467440737095516160', '200000000000000000000');
        $this->assertSame($sphinx->minid, '184467440737095516160');
        $this->assertSame($sphinx->maxid, '200000000000000000000');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetIdRangeWithMinLargerThanMaxShouldThrowException()
    {
        $sphinx = new SphinxClient();
        $sphinx->setIdRange('200000000000000000000', '184467440737095516160');
    }

    public function testSetFilter()
    {
        $sphinx = new SphinxClient();
        $sphinx->setFilter('attr', array(9001, '184467440737095516160'), true);
        $this->assertSame($sphinx->filters[0], array(
            'type' => SphinxClient::SPH_FILTER_VALUES,
            'attr' => 'attr',
            'exclude' => true,
            'values' => array(9001, '184467440737095516160')
        ));
    }

    public function testSetFilterRange()
    {
        $sphinx = new SphinxClient();
        $sphinx->setFilterRange('attr', 9001, '184467440737095516160', true);
        $this->assertSame($sphinx->filters[0], array(
            'type' => SphinxClient::SPH_FILTER_RANGE,
            'attr' => 'attr',
            'exclude' => true,
            'min' => 9001,
            'max' => '184467440737095516160'
        ));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetFilterRangeWithMinLargerThanMaxShouldThrowException()
    {
        $sphinx = new SphinxClient();
        $sphinx->setFilterRange('attr', 9001, 1337, true);
    }

    public function testSetFilterFloatRange()
    {
        $sphinx = new SphinxClient();
        $sphinx->setFilterFloatRange('attr', 12.34, 23.456, true);
        $this->assertSame($sphinx->filters[0], array(
            'type' => SphinxClient::SPH_FILTER_FLOATRANGE,
            'attr' => 'attr',
            'exclude' => true,
            'min' => 12.34,
            'max' => 23.456
        ));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetFilterFloatRangeWithMinLargerThanMaxShouldThrowException()
    {
        $sphinx = new SphinxClient();
        $sphinx->setFilterFloatRange('attr', 23.456, 12.34, true);
    }

    public function testSetGeoAnchor()
    {
        $sphinx = new SphinxClient();
        $sphinx->setGeoAnchor('lat', 'lng', 37.332, -122.031);
        $this->assertSame($sphinx->anchor, array(
            'attrlat' => 'lat',
            'attrlong' => 'lng',
            'lat' => 37.332,
            'long' => -122.031
        ));
    }

    public function testSetGroupBy()
    {
        $sphinx = new SphinxClient();
        $sphinx->setGroupBy('attr', SphinxClient::SPH_GROUPBY_ATTR, '@group asc');
        $this->assertSame($sphinx->groupby, 'attr');
        $this->assertSame($sphinx->groupfunc, SphinxClient::SPH_GROUPBY_ATTR);
        $this->assertSame($sphinx->groupsort, '@group asc');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetGroupByWithInvalidGroupByShouldThrowException()
    {
        $sphinx = new SphinxClient();
        $sphinx->setGroupBy('attr', 6);
    }

    public function testSetGroupDistinct()
    {
        $sphinx = new SphinxClient();
        $sphinx->setGroupDistinct('id');
        $this->assertSame($sphinx->groupdistinct, 'id');
    }

    public function testSetRetries()
    {
        $sphinx = new SphinxClient();
        $sphinx->setRetries(3, 10);
        $this->assertSame($sphinx->retrycount, 3);
        $this->assertSame($sphinx->retrydelay, 10);
    }

    public function testSetArrayResult()
    {
        $sphinx = new SphinxClient();
        $sphinx->setArrayResult(true);
        $this->assertTrue($sphinx->arrayresult);
    }

    public function testSetOverride()
    {
        $sphinx = new SphinxClient();
        $sphinx->setOverride('attr', SphinxClient::SPH_ATTR_INTEGER, array(1337 => 9001));
        $this->assertSame($sphinx->overrides['attr'], array(
            'attr' => 'attr',
            'type' => SphinxClient::SPH_ATTR_INTEGER,
            'values' => array(1337 => 9001)
        ));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetOverrideWithInvalidAttributeTypeShouldThrowException()
    {
        $sphinx = new SphinxClient();
        $sphinx->setOverride('attr', 7, array());
    }

    public function testSetSelect()
    {
        $sphinx = new SphinxClient();
        $sphinx->setSelect('*, @weight + 10 as myweight');
        $this->assertSame($sphinx->select, '*, @weight + 10 as myweight');
    }

    public function testResetFilters()
    {
        $sphinx = new SphinxClient();
        $sphinx->setFilter('attr', array(9001, '184467440737095516160'), true);
        $sphinx->setGeoAnchor('lat', 'lng', 37.332, -122.031);
        $sphinx->resetFilters();
        $this->assertSame($sphinx->filters, array());
        $this->assertSame($sphinx->anchor, array());
    }

    public function testResetGroupBy()
    {
        $sphinx = new SphinxClient();
        $sphinx->setGroupBy('attr', SphinxClient::SPH_GROUPBY_ATTR, '@group asc');
        $sphinx->setGroupDistinct('id');
        $sphinx->resetGroupBy();
        $this->assertSame($sphinx->groupby, '');
        $this->assertSame($sphinx->groupfunc, SphinxClient::SPH_GROUPBY_DAY);
        $this->assertSame($sphinx->groupsort, '@group desc');
        $this->assertSame($sphinx->groupdistinct, '');
    }

    public function testResetOverrides()
    {
        $sphinx = new SphinxClient();
        $sphinx->setOverride('attr', SphinxClient::SPH_ATTR_INTEGER, array(1337 => 9001));
        $sphinx->resetOverrides();
        $this->assertSame($sphinx->overrides, array());
    }

    public function testEscapeString()
    {
        $sphinx = new SphinxClient();
        $input = '\\()|#-!1@~ab"cde&/^$=';
        $expected = '\\\\\(\)\|#\-\!1\@\~ab\"cde\&\/\^\$\=';
        $this->assertSame($sphinx->escapeString($input), $expected);
    }

    /**
     * The following tests require a working Sphinx setup
     */

    /**
     * @dataProvider provideQuery
     */
    public function testQuery($query, $sphinx, $expected)
    {
        $results = $sphinx->query($query);
        foreach ($expected as $key => $value) {
            if ($key === 'matches') {
                $this->assertEquals(array_keys($results['matches']), $value);
            } else {
                $this->assertEquals($results[$key], $value);
            }
        }
    }

    private function getSphinxClient()
    {
        return SphinxClient::create()
            ->setMatchMode(SphinxClient::SPH_MATCH_ANY)
            ->setSortMode(SphinxClient::SPH_SORT_RELEVANCE)
            ->setRankingMode(SphinxClient::SPH_RANK_PROXIMITY_BM25)
            ->setConnectTimeout(2);
    }

    public function provideQuery()
    {
        return array(
            array(
                'a',
                $this->getSphinxClient()
                     ->setMatchMode(SphinxClient::SPH_MATCH_ALL),
                array(
                    'fields' => array('field1', 'field2'),
                    'attrs' => array('attr1' => 1, 'lat' => 5, 'long' => 5, 'stringattr' => 7),
                    'matches' => array('1', '2', '3', '4'),
                    'total' => 4,
                    'total_found' => 4,
                    'words' => array('a' => array('docs' => 4, 'hits' => 4))
                )
            ),
            array(
                'bb',
                $this->getSphinxClient(),
                array(
                    'matches' => array('4', '5', '1', '2', '3')
                )
            ),
            array(
                'ccc dddd',
                $this->getSphinxClient()
                     ->setMatchMode(SphinxClient::SPH_MATCH_PHRASE)
                     ->setSortMode(SphinxClient::SPH_SORT_ATTR_ASC, 'attr1'),
                array(
                    'matches' => array('3', '5', '4')
                )
            ),
            array(
                'bb ccc',
                $this->getSphinxClient()
                     ->setMatchMode(SphinxClient::SPH_MATCH_BOOLEAN)
                     ->setSortMode(SphinxClient::SPH_SORT_ATTR_DESC, 'attr1'),
                array(
                    'matches' => array('4', '2', '5', '3')
                )
            ),
            array(
                'bb ccc',
                $this->getSphinxClient()
                     ->setSortMode(SphinxClient::SPH_SORT_EXTENDED, '@relevance DESC, attr1 ASC'),
                array(
                    'matches' => array('5', '4', '3', '2', '1')
                )
            ),
            array(
                'bb',
                $this->getSphinxClient()
                     ->setLimits(0, 2),
                array(
                    'matches' => array('4', '5')
                )
            ),
            array(
                '@field1 bb @field2 ccc',
                $this->getSphinxClient()
                     ->setMatchMode(SphinxClient::SPH_MATCH_EXTENDED)
                     ->setLimits(0, 20),
                array(
                    'matches' => array('4', '5')
                )
            ),
            array(
                'bb ccc',
                $this->getSphinxClient()
                     ->setWeights(array(10, 2)),
                array(
                    'matches' => array('4', '5', '2', '3', '1')
                )
            ),
            array(
                'bb ccc',
                $this->getSphinxClient()
                     ->setIndexWeights(array('sphinxtest' => 2)),
                array(
                    'matches' => array('4', '5', '2', '3', '1')
                )
            ),
            array(
                'bb ccc',
                $this->getSphinxClient()
                     ->setFieldWeights(array('field1' => 10, 'field2' => 2)),
                array(
                    'matches' => array('4', '5', '2', '3', '1')
                )
            ),
            array(
                'xx',
                $this->getSphinxClient()
                     ->setIdRange(0, '18446744073709551615'),
                array(
                    'matches' => array('9223372036854775807')
                )
            ),
            array(
                'bb' . $this->utf16to8('2122'),
                $this->getSphinxClient()
                     ->setIdRange(0, 0xFFFFFFFF),
                array(
                    'matches' => array('4', '5', '1', '2', '3'),
                    'words' => array('bb' => array('docs' => 5, 'hits' => 8))
                )
            ),
            array(
                $keyword = $this->utf16to8('65e5672c8a9e'),
                $this->getSphinxClient()
                     ->setIdRange(0, 0xFFFFFFFF),
                array(
                    'matches' => array('6'),
                    'words' => array($keyword => array('docs' => 1, 'hits' => 1))
                )
            )
        );
    }

    public function testBuildExcerpts()
    {
        $sphinx = new SphinxClient();
        $results = $sphinx->buildExcerpts(
            array('bb bb ccc dddd', 'bb ccc dddd'),
            'sphinxtest',
            'ccc dddd'
        );
        $this->assertEquals($results, array(
            'bb bb <b>ccc</b> <b>dddd</b>',
            'bb <b>ccc</b> <b>dddd</b>'
        ));
    }

    public function testBuildExcerptsWithUnicode()
    {
        $sphinx = new SphinxClient();
        $keyword = $this->utf16to8('65e5672c8a9e');
        $results = $sphinx->buildExcerpts(
            array($keyword),
            'sphinxtest',
            $keyword
        );
        $this->assertEquals($results, array(
            "<b>$keyword</b>"
        ));
    }

    public function testBuildKeywords()
    {
        $sphinx = new SphinxClient();
        $results = $sphinx->buildKeywords('bb-dddd', 'sphinxtest', 1);
        $this->assertEquals($results, array(
            array(
                'tokenized' => 'bb',
                'normalized' => 'bb',
                'docs' => 5,
                'hits' => 8
            ),
            array(
                'tokenized' => 'dddd',
                'normalized' => 'dddd',
                'docs' => 3,
                'hits' => 3
            )
        ));
    }

    public function testBuildKeywordsWithUnicode()
    {
        $sphinx = new SphinxClient();
        $keyword = $this->utf16to8('65e5672c8a9e');
        $results = $sphinx->buildKeywords($keyword, 'sphinxtest', 1);
        $this->assertEquals($results, array(
            array(
                'tokenized' => $keyword,
                'normalized' => $keyword,
                'docs' => 1,
                'hits' => 1
            )
        ));
    }

    public function testGroupBy()
    {
        $sphinx = new SphinxClient();
        $sphinx->setGroupBy('lat', SphinxClient::SPH_GROUPBY_ATTR);
        $results = $sphinx->query('bb');
        $this->assertEquals($results['total'], 3);
        $sphinx->setGroupBy('attr1', SphinxClient::SPH_GROUPBY_ATTR);
        $results = $sphinx->query('bb');
        $this->assertEquals($results['total'], 5);
    }

    public function testUpdateAttributes()
    {
        $sphinx = new SphinxClient();
        $sphinx->setGroupBy('attr1', SphinxClient::SPH_GROUPBY_ATTR);
        $sphinx->updateAttributes('sphinxtest', array('attr1'), array(
            1 => array(10),
            2 => array(10),
            3 => array(20),
            4 => array(20)
        ));
        $results = $sphinx->query('bb');
        $this->assertEquals($results['total'], 3);

        // restore attributes
        $sphinx->updateAttributes('sphinxtest', array('attr1'), array(
            1 => array(2),
            2 => array(4),
            3 => array(1),
            4 => array(5)
        ));
    }

    public function testFilter()
    {
        $sphinx = new SphinxClient();
        $sphinx->setFilter('attr1', array(1, 2, 3));
        $results = $sphinx->query('bb');
        $this->assertEquals($results['total'], 3);
    }

    public function testFilterExclude()
    {
        $sphinx = new SphinxClient();
        $sphinx->setFilter('attr1', array(1, 2, 3), true);
        $results = $sphinx->query('bb');
        $this->assertEquals($results['total'], 2);
    }

    public function testFilterRange()
    {
        $sphinx = new SphinxClient();
        $sphinx->setFilterRange('attr1', 4, 5);
        $results = $sphinx->query('bb');
        $this->assertEquals($results['total'], 2);
    }

    public function testFilterRangeExclude()
    {
        $sphinx = new SphinxClient();
        $sphinx->setFilterRange('attr1', 4, 5, true);
        $results = $sphinx->query('bb');
        $this->assertEquals($results['total'], 3);
    }

    public function testFilterFloatRange()
    {
        $sphinx = new SphinxClient();
        $sphinx->setFilterFloatRange('lat', 0.2, 0.4);
        $results = $sphinx->query('bb');
        $this->assertEquals($results['total'], 3);
    }

    public function testFilterFloatRangeExclude()
    {
        $sphinx = new SphinxClient();
        $sphinx->setFilterFloatRange('lat', 0.2, 0.4, true);
        $results = $sphinx->query('bb');
        $this->assertEquals($results['total'], 2);
    }

    public function testFilterIdRange()
    {
        $sphinx = new SphinxClient();
        $sphinx->setIdRange(2, 4);
        $results = $sphinx->query('bb');
        $this->assertEquals(array_keys($results['matches']), array('4', '2', '3'));
    }

    public function testGeoAnchor()
    {
        $sphinx = new SphinxClient();
        $sphinx->setGeoAnchor('lat', 'long', 0.4, 0.4)
               ->setMatchMode(SphinxClient::SPH_MATCH_EXTENDED)
               ->setSortMode(SphinxClient::SPH_SORT_EXTENDED, '@geodist desc')
               ->setFilterFloatRange('@geodist', 0, 1934127);
        $results = $sphinx->query('a');
        $this->assertEquals(array_keys($results['matches']), array('1', '3', '4'));
    }

    public function testAddQuery()
    {
        $sphinx = new SphinxClient();
        $sphinx->addQuery('ccc');
        $sphinx->addQuery('dddd');
        $results = $sphinx->runQueries();
        $this->assertEquals(count($results), 2);
    }

    public function testAddQueryWithError()
    {
        $sphinx = new SphinxClient();
        $sphinx->setMatchMode(SphinxClient::SPH_MATCH_EXTENDED);
        $sphinx->addQuery('@ccc');
        $sphinx->addQuery('dddd');
        $results = $sphinx->runQueries();
        $this->assertEquals(count($results), 2);
        $this->assertGreaterThan(0, strlen($results[0]['error']));
    }

    public function testStatus()
    {
        $sphinx = new SphinxClient();
        $status = $sphinx->status();
        $this->assertGreaterThan(0, count($status));
    }

    private function utf16to8($code)
    {
        return mb_convert_encoding(pack('H*', $code), 'UTF-8', 'UTF-16BE');
    }
}
