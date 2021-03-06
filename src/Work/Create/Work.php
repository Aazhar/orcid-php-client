<?php


namespace Orcid\Work\Create;

use DOMDocument;
use DOMElement;
use DOMNode;
use Orcid\Work\OAwork;
use Orcid\Work\ExternalId;

class Work extends OAwork
{
    const FULL_NAME = 'fullName';
    const ORCID_ID  = 'orcidID';
    const ROLE      = 'role';
    const SEQUENCE  = 'sequence';
    const HOSTNAME  = 'orcid.org';

    public static $namespaceWork= "http://www.orcid.org/ns/work";
    public static $namespaceCommon = "http://www.orcid.org/ns/common";
    public static $namespacebulk ="http://www.orcid.org/ns/bulk";

    /**
     * @var string
     */
    protected $journalTitle;
    /**
     * @var string
     */
    protected $shortDescription;
    /**
     * @var string
     */
    protected $citation;
    /**
     * @var string []
     */
    protected $authors;
    /**
     * @var string
     */
    protected $principalAuthors;
    /**
     * @var string
     */
    protected $languageCode;
    /**
     * @var string
     */
    protected $citationType;

    /**
     * @var string
     */
    protected $country;
    /**
     * @var string
     */
    protected $workUrl;

    public function __construct()
    {
    }

    /**
     * An empty fullName string value will not be added
     * to be sure to add an author check on your side that his full name is not empty.
     * @param string $fullName
     * @param string $role
     * @param string $orcidID
     * @param string $sequence
     * @return $this
     */
    public function addAuthor(string $fullName,string $role='author',string $orcidID='', string $sequence='')
    {
        if(!empty($fullName)){
            $this->authors []= [self::FULL_NAME =>$fullName, self::ROLE=>$role,self::ORCID_ID =>$orcidID, self::SEQUENCE =>$sequence];
        }
        return $this;
    }

    /**
     * An empty string value will not be added
     * @param string $journalTitle
     * @return $this
     */
    public function setJournalTitle(string $journalTitle)
    {
        if(!empty($journalTitle)) {
            $this->journalTitle = $journalTitle;
        }
        return $this;
    }

    /**
     * An empty string value will not be added
     * @param string $shortDescription
     * @return $this
     * @throws \Exception
     */
    public function setShortDescription(string $shortDescription)
    {
        if(mb_strlen($shortDescription)>5000){
            throw new \Exception('The short description length must not be than 5000 characters');
        }elseif (!empty($shortDescription)) {
            $this->shortDescription = $shortDescription;
        }
        return $this;
    }

    /**
     * an exception is thrown if you try to add invalid value
     * An empty string value will not be added
     * @param string $languageCode
     * @return $this
     * @throws \Exception
     */
    public function setLanguageCode(string $languageCode)
    {
       if(!empty($languageCode)&&in_array(strtolower($languageCode) ,self::LANGAGE_CODES)){
           $this->languageCode = $languageCode;
       }elseif(!empty($languageCode)&&!in_array(strtolower($languageCode) ,self::LANGAGE_CODES)){
           throw new \Exception("The langage code must be a string of two or three character and must respect ISO 3166 rules for country ");
       }
        return $this;
    }

    /**
     * An empty string value will not be added
     * @param string|string[] $principalAuthors
     */
    public function setPrincipalAuthors($principalAuthors)
    {
        if(!empty($principalAuthors)){
            $this->principalAuthors = $principalAuthors;
        }
        return $this;
    }

    /**
     * An empty string value will not be added like citation
     * @param string $citation
     * @param string $citationType
     * @return $this
     */
    public function setCitation(string $citation,$citationType='formatted-unspecified')
    {
        if(!empty($citation)){
            $this->citation = $citation;
            if(empty($this->citationType)){
                $this->setCitationType($citationType);
            }
        }
        return $this;
    }

    /**
     * 1- by default your citation type will be formatted-unspecified
     * if you add citation without citation-type.
     * 2-it makes no sense to add citation type without adding citation
     * @param string $citationType
     * @return $this
     * @throws \Exception
     */
    public function setCitationType(string $citationType)
    {
        if (!empty($citationType) && in_array(strtolower($citationType),self::CITATION_FORMATS)) {
            $this->citationType = $citationType;
        }elseif (!empty($citationType)){
            throw new \Exception("The citation format : ".$citationType."  is not valid");
        }
        return $this;
    }


    /**
     * to be sure to add a country check on your side that it is not empty.
     * An empty string value will not be added
     * @param string $country
     * @return $this
     */
    public function setCountry(string $country)
    {
     if(!empty($country)){
         $this->country = $country;
     }
        return $this;
    }

    /**
     * to be sure to add a work url check on your side that it is not empty.
     * An empty string value will not be added
     * @param string $workUrl
     * @return $this
     */
    public function setWorkUrl(string $workUrl)
    {
        if(!empty($workUrl)){
            $this->workUrl = $workUrl;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    /**
     * @return string
     */
    public function getCitationType()
    {
        return $this->citationType;
    }

    /**
     * @return string
     */
    public function getWorkUrl()
    {
        return $this->workUrl;
    }

    /**
     * @return string
     */
    public function getCitation()
    {
        return $this->citation;
    }

    /**
     * @return string
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * @return string
     */
    public function getJournalTitle()
    {
        return $this->journalTitle;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getLanguageCode()
    {
        return $this->languageCode;
    }
    /**
     * @param DOMDocument $dom
     * @param DOMNode $work
     * @return DOMNode
     */
    public function addMetaToWorkNode (DOMDocument $dom,DOMNode $work)
    {
        $this->checkMetaValueAndThrowExceptionIfNecessary();

        if( isset($this->putCode)){
            $work->setAttribute("put-code", (int)$this->putCode  );
        }

        //add work title
        $workTitle = $work->appendChild( $dom->createElementNS(self::$namespaceWork, "title") );
        $title = $workTitle->appendChild( $dom->createElementNS(self::$namespaceCommon, "title") );
        $title->appendChild( $dom->createCDATASection( $this->title ) ) ;

        if(isset($this->subTitle)){
            $subtitle = $workTitle->appendChild($dom->createElementNS(self::$namespaceCommon,"subtitle") );
            $subtitle = $subtitle->appendChild($dom->createCDATASection($this->subTitle));
        }

        //translatedTitleLanguageCode is required to send translatedTitle
        if(isset($this->translatedTitle) && isset($this->translatedTitleLanguageCode)){
            $translatedTitle = $workTitle->appendChild( $dom->createElementNS(self::$namespaceCommon, "translated-title"));
            $translatedTitle->appendChild($dom->createCDATASection($this->translatedTitle));
            $translatedTitle->setAttribute('language-code',$this->translatedTitleLanguageCode);
        }

        if(isset($this->journalTitle)){
            $journalTitle = $work->appendChild( $dom->createElementNS(self::$namespaceWork,"journal-title") );
            $journalTitle->appendChild( $dom->createCDATASection( $this->journalTitle) );
        }

        if(isset($this->shortDescription)){
            $shortDescription = $work->appendChild( $dom->createElementNS(self::$namespaceWork,"short-description") );
            $shortDescription->appendChild( $dom->createCDATASection($this->shortDescription ) ) ;
        }

        if(isset($this->citation)){
            $work->appendChild( $this->nodeCitation($dom,$this->citationType,$this->citation));
        }

        // add work Type
        $work->appendChild( $dom->createElementNS(self::$namespaceWork, "type", $this->type) );

        // add publication date
        if(isset($this->publicationDate)){
            $year=$this->publicationDate[self::YEAR];
            $month =$this->publicationDate[self::MONTH];
            $day=$this->publicationDate[self::DAY];
            $work->appendChild($this->dateNode($dom,$year,$month,$day));
        }

        //add external ident
        $externalIds = $work->appendChild( $dom->createElementNS(self::$namespaceCommon, "external-ids" ) );
            foreach ($this->externals as $externalId){
                /**
                 * @var ExternalId $externalId
                 */
                $idType=$externalId->getIdType();
                $idValue=$externalId->getIdValue();
                $idUrl=$externalId->getIdUrl();
                $relationship=$externalId->getIdRelationship();
                $externalIds->appendChild( $this->externalIdNode($dom, $idType, $idValue,$idUrl,$relationship)) ;

            }

        if(isset($this->workUrl))
        {
            $work->appendChild( $dom->createElementNS(self::$namespaceWork, "url",$this->workUrl ) );
        }

       //add authors
        if(isset($this->authors) || isset($this->principalAuthors)){
            $contributors = $work->appendChild( $dom->createElementNS(self::$namespaceWork,"contributors") );
            if(isset($this->authors) && is_array($this->authors)){
                foreach($this->authors as $author){
                    $contributors->appendChild( $this->nodeContributor($dom,$author[self::FULL_NAME],$author[self::ROLE],$author[self::ORCID_ID],$author[self::SEQUENCE]) );
                }
            }

            if(isset($this->principalAuthors)){
                foreach($this->principalAuthors as $name){
                    $contributors->appendChild( $this->nodeContributor($dom, $name, "principal-investigator") );
                }
            }elseif (isset($this->principalAuthors) && is_string($this->principalAuthors)){
                $contributors->appendChild( $this->nodeContributor($dom, $this->principalAuthors, "principal-investigator") );
            }
        }

        if(isset($this->languageCode))
        {
            $work->appendChild( $dom->createElementNS(self::$namespaceCommon, "language-code",$this->languageCode ) );
        }

        if(isset($this->country))
        {
            $work->appendChild( $dom->createElementNS(self::$namespaceCommon, "country",$this->country ) );
        }

        return $work;
    }

    /**
     * built an external identifier node
     * @param DOMDocument $dom
     * @param string $type
     * @param string $value
     * @param string $relationship
     * @param string $url
     * @return DOMNode
     */

    protected function externalIdNode(DOMDocument $dom, string $type, string $value, string $url="",string $relationship="self")
    {
        $externalIdNode = $dom->createElementNS(self::$namespaceCommon, "external-id");
        //Type Node
        $externalIdTypeNode=$dom->createElementNS(self::$namespaceCommon,"external-id-type");
        $externalIdTypeNodeValue=$dom->createTextNode($type);
        $externalIdTypeNode->appendChild($externalIdTypeNodeValue);
        $externalIdNode->appendChild( $externalIdTypeNode);
       // Value Node
        $externalIdValueNode=$dom->createElementNS(self::$namespaceCommon, "external-id-value");
        $externalIdValueNodeValue=$dom->createTextNode($value) ;
        $externalIdValueNode->appendChild($externalIdValueNodeValue);
        $externalIdNode->appendChild($externalIdValueNode);

        if(!empty($url)){
            //url Node
            $externalIdUrlNode=$dom->createElementNS(self::$namespaceCommon, "external-id-url" );
            $externalIdUrlNodeValue=$dom->createTextNode($url);
            $externalIdUrlNode->appendChild($externalIdUrlNodeValue);
            $externalIdNode->appendChild($externalIdUrlNode);
        }

        $externalIdNode->appendChild( $dom->createElementNS(self::$namespaceCommon,"external-id-relationship",$relationship) );

        return $externalIdNode ;
    }

    /**
     * built an author node
     * @param DOMDocument $dom
     * @param string $name
     * @param string $role
     * @return DOMNode
     */
    protected function nodeContributor(DOMDocument $dom, string $name, string $role,string $orcidID='',string $sequence='')
    {
        $contributor = $dom->createElementNS(self::$namespaceWork, "contributor");
        if(!empty($orcidID)){
            $contributorOrcid=$contributor->appendChild($dom->createElementNS(self::$namespaceWork,"contributor-orcid"));
            $contributorOrcid->appendChild($dom->createElementNS(self::$namespaceWork,"uri",'https://'.self::HOSTNAME.'/'.$orcidID));
            $contributorOrcid->appendChild($dom->createElementNS(self::$namespaceWork,"path",$orcidID));
            $contributorOrcid->appendChild($dom->createElementNS(self::$namespaceWork,"host",self::HOSTNAME));
        }
        $creditName = $contributor->appendChild( $dom->createElementNS(self::$namespaceWork,"credit-name"));
        $creditName->appendChild( $dom->createCDATASection( $name ) ) ;
        $attributes = $contributor->appendChild( $dom->createElementNS( self::$namespaceWork,"contributor-attributes" ));
        $attributes->appendChild($dom->createElementNS( self::$namespaceWork , "contributor-role", $role) );
        if(!empty($sequence)){
            $attributes->appendChild($dom->createElementNS( self::$namespaceWork , "contributor-sequence", $sequence));
        }
        return $contributor ;
    }

    /**
     * built an citation node
     * @param DOMDocument $dom
     * @param string $type
     * @param string $value
     * @return DOMElement
     */
    protected function nodeCitation(DOMDocument $dom, string $type,string $value){

        $citation = $dom->createElementNS(self::$namespaceWork, "citation");
        if($type!==''){
            $citation->appendChild($dom->createElementNS(self::$namespaceWork, "citation-type",$type));
        }
        $citationValue=$dom->createElementNS(self::$namespaceWork, "citation-value");
        $citationValue->appendChild($dom->createTextNode($value));
        $citation->appendChild($citationValue);
        return $citation;
    }

    /**
     * built an date Node
     * @param DOMDocument $dom
     * @param string  $year
     * @param string  $month
     * @param string  $day
     * @return DOMNode
     */
    protected function dateNode(DOMDocument $dom, string $year, string $month='', string $day=''): DOMNode
    {
        $valiDate=1;
        $publicationDate =  $dom->createElementNS(self::$namespaceCommon, "publication-date");

        if (strlen($month) === 1) {
            $month = '0' . $month;
        }
        if (strlen($day )=== 1) {
            $day =  '0' . $day;
        }

        if(strlen($year)===4){
            $publicationDate->appendChild( $dom->createElementNS(self::$namespaceCommon, "year", $year ) );
            $valiDate++;
        }

        if($month!==''&&(int)$month>0 &&(int)$month<13 && $valiDate>1) {
            $publicationDate->appendChild($dom->createElementNS(self::$namespaceCommon, "month", $month));
            $valiDate++;
        }

        if($day!==''&&(int)$day>0 &&(int)$day<32 && $valiDate>2)  {
            $publicationDate->appendChild( $dom->createElementNS(self::$namespaceCommon, "day", $day ) );
        }
        return  $publicationDate ;
    }


    /**
     * @return false|string
     */
    public function getXMLData()
    {
        $dom = self::getNewOrcidCommonDomDocument();
        $workNode= $dom->appendChild($dom->createElementNS(self::$namespaceWork,"work:work"));
        $dom->createAttributeNS(self::$namespaceCommon, "common:common");
        $workNode->setAttributeNS("http://www.w3.org/2001/XMLSchema-instance", "xsi:schemaLocation", self::$namespaceWork . "/ work-2.0.xsd ");
        $this->addMetaToWorkNode($dom,$workNode);
        return $dom->saveXML() ;
    }

    /**
     * @param bool $preserveWhiteSpace
     * @param bool $formatOutput
     * @return DOMDocument
     */
    public static function getNewOrcidCommonDomDocument(bool $preserveWhiteSpace=false,bool $formatOutput=true){
        $dom = new DOMDocument("1.0", "UTF-8");
        $dom->preserveWhiteSpace = $preserveWhiteSpace;
        $dom->formatOutput = $formatOutput;
        return $dom;
    }

    /**
     * @throws \Exception
     */
    public function checkMetaValueAndThrowExceptionIfNecessary()
    {
         $reponse="";
        if(empty($this->title)) {
            $reponse .=" Title recovery failed: Title value cannot be empty";
        }
        if(empty($this->type)) {
            $reponse .=" Work Type recovery failed: Type value cannot be empty";
        }
        if(empty($this->externals)) {
            $reponse .=" externals Ident recovery failed: externals values cannot be empty";
        }
        if($reponse!==""){
            throw new \Exception($reponse);
        }
    }
}
